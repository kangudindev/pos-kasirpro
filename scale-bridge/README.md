# POS KasirPro - Scale Bridge Service

## Installation

```bash
cd scale-bridge
npm install
```

## Running

```bash
# Development
npm run dev

# Production
npm start

# With Docker
docker-compose up node
```

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| BRIDGE_PORT | 3000 | HTTP/WebSocket port |
| LARAVEL_URL | http://localhost:8000 | Laravel app URL |

## API Endpoints

### Health Check
```
GET /health
```

### List Connected Scales
```
GET /scales
```

### Connect Scale
```
POST /scales/connect
Body: {
    "id": 1,
    "connectionType": "serial|tcpip",
    "port": "COM3",
    "baudRate": 9600,
    "ipAddress": "192.168.1.100",
    "portNumber": 5000
}
```

### Disconnect Scale
```
POST /scales/disconnect
Body: { "id": 1 }
```

### Get Weight
```
POST /scales/weight
Body: { "scaleId": 1 }
```

### Send PLU
```
POST /scales/plu
Body: { "scaleId": 1, "pluCode": "001", "price": 15000 }
```

## WebSocket Messages

### Request Weight
```json
{
    "type": "request_weight",
    "scaleId": 1
}
```

### Weight Response
```json
{
    "type": "weight",
    "scaleId": 1,
    "weight": 1.5,
    "unit": "kg"
}
```

### Send PLU
```json
{
    "type": "send_plu",
    "scaleId": 1,
    "pluCode": "001",
    "price": 15000
}
```

## Supported Protocols

### CAS Protocol
- Weight request: `R\r\n`
- PLU send: `P{plu_code}\r\n`
- Weight response: `0000000.00g0000.00g      `

### Acom Protocol
- Similar to CAS with minor differences

### Custom ASCII
- Configurable via protocol settings
