/**
 * POS KasirPro - Scale Bridge Service
 * 
 * This service bridges communication between:
 * - Web POS (via WebSocket)
 * - Digital Scales (via RS232/TCP-IP)
 * - Label Printers (via RS232/TCP-IP)
 */

const express = require('express');
const http = require('http');
const WebSocket = require('ws');
const { SerialPort } = require('serialport');
const { ReadlineParser } = require('@serialport/parser-readline');
const net = require('net');

const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

// Configuration
const CONFIG = {
    port: process.env.BRIDGE_PORT || 3000,
    laravelUrl: process.env.LARAVEL_URL || 'http://localhost:8000',
};

// Connected scales
const connectedScales = new Map();
const connectedClients = new Map();

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        scales: Array.from(connectedScales.keys()),
        clients: Array.from(connectedClients.keys()),
    });
});

// Get connected scales
app.get('/scales', (req, res) => {
    const scales = [];
    connectedScales.forEach((scale, id) => {
        scales.push({
            id,
            name: scale.config.name,
            connectionType: scale.config.connectionType,
            status: scale.isConnected ? 'connected' : 'disconnected',
        });
    });
    res.json(scales);
});

// Connect to scale
app.post('/scales/connect', (req, res) => {
    const { id, connectionType, port, baudRate, ipAddress, portNumber } = req.body;

    try {
        connectScale(id, {
            name: `Scale ${id}`,
            connectionType,
            port,
            baudRate: baudRate || 9600,
            ipAddress,
            portNumber: portNumber || 5000,
        });

        res.json({ success: true, message: 'Scale connected' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Disconnect scale
app.post('/scales/disconnect', (req, res) => {
    const { id } = req.body;

    if (connectedScales.has(id)) {
        const scale = connectedScales.get(id);
        if (scale.connection) {
            scale.connection.close();
        }
        connectedScales.delete(id);
    }

    res.json({ success: true, message: 'Scale disconnected' });
});

// Request weight from scale
app.post('/scales/weight', (req, res) => {
    const { scaleId } = req.body;

    const scale = connectedScales.get(scaleId);
    if (!scale || !scale.isConnected) {
        return res.status(400).json({ success: false, message: 'Scale not connected' });
    }

    // Send weight request
    scale.connection.write('R\r\n');

    // Wait for response
    const timeout = setTimeout(() => {
        res.status(408).json({ success: false, message: 'Timeout waiting for weight' });
    }, 5000);

    scale.once('weight', (weight) => {
        clearTimeout(timeout);
        res.json({ success: true, weight, unit: 'kg' });
    });
});

// Send PLU to scale
app.post('/scales/plu', (req, res) => {
    const { scaleId, pluCode, price } = req.body;

    const scale = connectedScales.get(scaleId);
    if (!scale || !scale.isConnected) {
        return res.status(400).json({ success: false, message: 'Scale not connected' });
    }

    // CAS protocol: Send PLU
    const command = `P${pluCode}\r\n`;
    scale.connection.write(command);

    res.json({ success: true, message: 'PLU sent to scale' });
});

// WebSocket connection handler
wss.on('connection', (ws) => {
    const clientId = Date.now().toString();
    connectedClients.set(clientId, ws);

    console.log(`Client connected: ${clientId}`);

    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);

            switch (data.type) {
                case 'request_weight':
                    handleWeightRequest(ws, data);
                    break;
                case 'send_plu':
                    handlePluSend(ws, data);
                    break;
                case 'print_label':
                    handleLabelPrint(ws, data);
                    break;
            }
        } catch (error) {
            ws.send(JSON.stringify({ type: 'error', message: error.message }));
        }
    });

    ws.on('close', () => {
        connectedClients.delete(clientId);
        console.log(`Client disconnected: ${clientId}`);
    });
});

// Handle weight request via WebSocket
function handleWeightRequest(ws, data) {
    const { scaleId } = data;
    const scale = connectedScales.get(scaleId);

    if (!scale || !scale.isConnected) {
        ws.send(JSON.stringify({ type: 'error', message: 'Scale not connected' }));
        return;
    }

    scale.connection.write('R\r\n');

    const timeout = setTimeout(() => {
        ws.send(JSON.stringify({ type: 'error', message: 'Timeout waiting for weight' }));
    }, 5000);

    scale.once('weight', (weight) => {
        clearTimeout(timeout);
        ws.send(JSON.stringify({ type: 'weight', weight, unit: 'kg' }));
    });
}

// Handle PLU send via WebSocket
function handlePluSend(ws, data) {
    const { scaleId, pluCode, price } = data;
    const scale = connectedScales.get(scaleId);

    if (!scale || !scale.isConnected) {
        ws.send(JSON.stringify({ type: 'error', message: 'Scale not connected' }));
        return;
    }

    const command = `P${pluCode}\r\n`;
    scale.connection.write(command);

    ws.send(JSON.stringify({ type: 'plu_sent', pluCode }));
}

// Handle label print via WebSocket
function handleLabelPrint(ws, data) {
    const { printerId, labelData } = data;

    // Send to printer (simplified)
    ws.send(JSON.stringify({ type: 'label_printed', printerId }));
}

// Connect to scale
function connectScale(id, config) {
    let connection;
    let isConnected = false;

    if (config.connectionType === 'serial') {
        // RS232 Serial connection
        connection = new SerialPort({
            path: config.port,
            baudRate: config.baudRate,
        });

        const parser = connection.pipe(new ReadlineParser({ delimiter: '\r\n' }));

        parser.on('data', (data) => {
            handleScaleData(id, data);
        });

    } else if (config.connectionType === 'tcpip') {
        // TCP/IP connection
        connection = new net.Socket();
        connection.connect(config.portNumber, config.ipAddress, () => {
            isConnected = true;
            console.log(`Scale ${id} connected via TCP/IP`);
        });

        connection.on('data', (data) => {
            handleScaleData(id, data.toString());
        });
    }

    connection.on('error', (err) => {
        console.error(`Scale ${id} error:`, err.message);
        isConnected = false;
        broadcastToClients({ type: 'scale_disconnected', scaleId: id });
    });

    connection.on('close', () => {
        isConnected = false;
        broadcastToClients({ type: 'scale_disconnected', scaleId: id });
    });

    connectedScales.set(id, {
        config,
        connection,
        isConnected,
        emit: (event, data) => {}, // Simplified
        once: (event, callback) => {}, // Simplified
    });

    broadcastToClients({ type: 'scale_connected', scaleId: id });
}

// Handle data from scale
function handleScaleData(scaleId, data) {
    console.log(`Scale ${scaleId} data:`, data);

    // CAS protocol weight response parsing
    // Format: "0000000.00g0000.00g      "
    const weightMatch = data.match(/(\d+\.\d+)g/);
    if (weightMatch) {
        const weight = parseFloat(weightMatch[1]);
        broadcastToClients({ type: 'weight', scaleId, weight, unit: 'kg' });
    }
}

// Broadcast to all connected WebSocket clients
function broadcastToClients(data) {
    const message = JSON.stringify(data);
    connectedClients.forEach((client) => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(message);
        }
    });
}

// Start server
server.listen(CONFIG.port, () => {
    console.log(`Scale Bridge Service running on port ${CONFIG.port}`);
    console.log(`WebSocket server ready`);
});
