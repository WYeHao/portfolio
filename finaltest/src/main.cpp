#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <SPI.h>
#include <MFRC522.h>
#include <WebServer.h>

// WiFi 
const char* ssid     = "Newera2G";
const char* password = "newera123456";  
const char* serverName = "http://192.168.0.216/july25iot/post-data.php";

String apiKeyValue = "tPmAT5Ab3j7F9";

// OLED
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);
#define OLED_SDA 21
#define OLED_SCL 22
float totalPrice = 0.0;

// RC522
#define RST_PIN  4
#define SS_PIN   5
MFRC522 rfid(SS_PIN, RST_PIN);

// Buzzer
#define BUZZER_PIN 15

WebServer server(80);

// Auto refresh 2 second
unsigned long lastUpdate = 0;  
const long updateInterval = 2000;

void setup() {
  Serial.begin(115200);

  // WiFi
  WiFi.disconnect(true);
  delay(1000);
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());

  // OLED
  Wire.begin(OLED_SDA, OLED_SCL);
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("OLED init failed!");
    for (;;);
  }
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 0);
  display.println("Please Scan Item");
  display.display();

  // RC522
  SPI.begin();
  rfid.PCD_Init();

  // Buzzer
  pinMode(BUZZER_PIN, OUTPUT);

  // WebServer
  server.on("/reset", HTTP_GET, []() {
    if (server.hasArg("ok")) {
      totalPrice = 0.0;

      display.clearDisplay();
      display.setCursor(0, 0);
      display.println("Payment successful!");
      display.display();
      delay(1500);

      display.clearDisplay();
      display.setCursor(0, 0);
      display.println("Please Scan Item");
      display.display();

      server.send(200, "Reset Done");
      Serial.println("Payment successful");
    } else {
      server.send(400, "Missing parameter");
    }
  });

  server.begin();
  Serial.println("HTTP server started");

  Serial.println("Please scan item...");
}

void loop() {
  server.handleClient();

  // Auto refresh
  if (millis() - lastUpdate > updateInterval) {
    lastUpdate = millis();

    if (WiFi.status() == WL_CONNECTED) {
      HTTPClient http;
      http.begin("http://192.168.1.17/july25iot/cart-total.php");
      int httpCode = http.GET();
      if (httpCode == 200) {
        String totalStr = http.getString();
        totalStr.trim();
        totalPrice = totalStr.toFloat();

        // OLED
        display.clearDisplay();
        display.setCursor(0, 0);
        display.println("Please Scan Item");

        display.setCursor(0, 50);
        display.print("Total: RM ");
        display.println(totalPrice, 2);
        display.display();
      }
      http.end();
    }
  }

  // RFID
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    return;
  }

  // RFID UID
  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    uid += String(rfid.uid.uidByte[i] < 0x10 ? "0" : "");
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();

  Serial.print("Card UID: ");
  Serial.println(uid);

  tone(BUZZER_PIN, 1000, 200);  

  String productName = "Unknown Item";
  float price = 0.0;

  // HTTP
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String httpRequestData = "api_key=" + apiKeyValue + "&uid=" + uid;
    int httpResponseCode = http.POST(httpRequestData);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println(response);

      // Product
      int start = response.indexOf("Product: ");
      if (start >= 0) {
        start += 9;
        int end = response.indexOf("|", start);
        if (end == -1) end = response.length();
        productName = response.substring(start, end);
        productName.trim();
      }

      // Price
      start = response.indexOf("Price: ");
      if (start >= 0) {
        start += 7;
        int end = response.indexOf("|", start);
        if (end == -1) end = response.length();
        String priceStr = response.substring(start, end);
        priceStr.trim();

        if (priceStr.startsWith("RM")) {
          priceStr = priceStr.substring(2);
          priceStr.trim();
        }

        price = priceStr.toFloat();
      }

      // Total
      int totalStart = response.indexOf("Total: ");
      if (totalStart >= 0) {
        totalStart += 7;
        int totalEnd = response.indexOf("|", totalStart);
        if (totalEnd == -1) totalEnd = response.length();
        String totalStr = response.substring(totalStart, totalEnd);
        totalStr.trim();

        if (totalStr.startsWith("RM")) {
          totalStr = totalStr.substring(2);
          totalStr.trim();
        }

        totalPrice = totalStr.toFloat();

        HTTPClient httpTotal;
        httpTotal.begin("http://192.168.0.216/july25iot/cart-total.php");
        int httpCodeTotal = httpTotal.GET();
        if (httpCodeTotal == 200) {
          String totalStr = httpTotal.getString();
          totalStr.trim();
          totalPrice = totalStr.toFloat();
          Serial.print("Grand Total: RM ");
          Serial.println(totalPrice, 2);
        }
        httpTotal.end();
      }

    } else {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }
    http.end();
  } else {
    Serial.println("WiFi disconnected, cannot send data!");
  }
  
  // OLED
  display.clearDisplay();
  display.setCursor(0, 0);
  display.print("Product: ");
  display.setCursor(70, 0);
  display.println(productName);

  display.setCursor(0, 15);
  display.print("Price:");
  display.setCursor(70, 15);
  display.print("RM ");
  display.println(price, 2);

  display.setCursor(0, 50);
  display.print("Total:");
  display.setCursor(70, 50);
  display.print("RM ");
  display.println(totalPrice, 2);

  display.display();
  delay(2000);

  display.clearDisplay();
  display.setCursor(0, 0);
  display.println("Please Scan Item");
  display.display();

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
}
