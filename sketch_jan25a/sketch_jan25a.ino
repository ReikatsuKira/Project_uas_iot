#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

#define MQ3_PIN   A0
#define TRIG_PIN D5
#define ECHO_PIN D6

const char* ssid = "surya";
const char* password = "12345678";

// IP laptop / server Laravel
String serverURL = "http://10.85.137.13:8000/sensor";

void setup() {
  Serial.begin(9600);

  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);

  WiFi.begin(ssid, password);
  Serial.print("Connecting WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWiFi Connected");
}

void loop() {
  // ===== MQ-3 =====
  int mq3Value = analogRead(MQ3_PIN);

  // ===== Ultrasonic =====
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  long duration = pulseIn(ECHO_PIN, HIGH, 30000);
  float distance = duration * 0.034 / 2;

  // ===== KIRIM KE SERVER =====
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    WiFiClient client;

    http.begin(client, serverURL);
    http.addHeader("Content-Type", "application/json");

    String jsonData = "{";
    jsonData += "\"gas\":" + String(mq3Value) + ",";
    jsonData += "\"jarak\":" + String(distance);
    jsonData += "}";

    int httpResponseCode = http.POST(jsonData);

    Serial.print("Send Data: ");
    Serial.println(jsonData);
    Serial.print("Response: ");
    Serial.println(httpResponseCode);

    http.end();
  }

  delay(2000); // kirim tiap 2 detik
}
