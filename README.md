# portfolio

# RFID Shopping Cart System

## System Architecture

### Workflow

| Step | Device | File Type | Action |
|------|--------|-----------|--------|
| 1️⃣  | ESP32  | C++ (Main) | RFID scan card, get UID |
| 2️⃣  | ESP32  | C++ (Main) | WiFi POST request → post-data.php |
| 3️⃣  | Server | PHP        | Receive UID, query database |
| 4️⃣  | Server | PHP        | Return product name, price, total to ESP32 |
| 5️⃣  | ESP32  | C++ (Main) | Receive response, display on OLED |
| 6️⃣  | Web    | PHP        | Display shopping cart list |
| 7️⃣  | Web    | PHP        | User click +/- / delete button to modify database |
