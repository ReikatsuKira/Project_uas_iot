#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Servo.h>

// ================= WIFI =================
const char* ssid     = "surya";
const char* password = "12345678";
const char* server   = "http://10.85.137.173:8000";

// ================= PIN ==================
#define SS_PIN     D8
#define RST_PIN    D0
#define SERVO_PIN  D4
#define TRIG_PIN   D1
#define ECHO_PIN   D2
#define BUZZER_PIN D3
#define GAS_PIN    A0

// ================= OBJEK =================
MFRC522 rfid(SS_PIN, RST_PIN);
Servo servo;

// ================= KONFIG (DINAMIS) =================
int gasNormal  = 500;   // default fallback
int gasDarurat = 600;   // default fallback

#define SERVO_OPEN_ANGLE  180
#define SERVO_CLOSE_ANGLE 0

const unsigned long SERVO_OPEN_TIME   = 3000;
const unsigned long SERIAL_INTERVAL   = 1000;
const unsigned long SENSOR_SEND_TIME  = 3000;
const unsigned long CONFIG_FETCH_TIME = 5000;

// ================= VAR =================
bool gasTriggered = false;
bool servoOpen = false;
bool servoByGas = false;
bool rfidBusy = false;

unsigned long servoTimer  = 0;
unsigned long serialTimer = 0;
unsigned long sensorTimer = 0;
unsigned long configTimer = 0;

// ================= SETUP =================
void setup() {
  Serial.begin(9600);

  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  servo.attach(SERVO_PIN);
  servo.write(SERVO_CLOSE_ANGLE);

  SPI.begin();
  rfid.PCD_Init();

  WiFi.begin(ssid, password);
  Serial.print("Connecting WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Connected");
}

// ================= LOOP =================
void loop() {
  updateGasConfig();
  bacaGas();
  bacaRFID();
  kontrolServo();
  tampilSerial();
  kirimSensorPeriodik();
}

// ================= AMBIL KONFIG DARI LARAVEL =================
void updateGasConfig() {
  if (millis() - configTimer < CONFIG_FETCH_TIME) return;
  configTimer = millis();

  if (WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  WiFiClient client;

  http.begin(client, String(server) + "/iot/gas-config");
  int code = http.GET();

  if (code == 200) {
    StaticJsonDocument<200> doc;
    DeserializationError err = deserializeJson(doc, http.getString());

    if (!err) {
      gasNormal  = doc["gas_normal"];
      gasDarurat = doc["gas_darurat"];

      Serial.print("Config updated → Normal: ");
      Serial.print(gasNormal);
      Serial.print(" | Darurat: ");
      Serial.println(gasDarurat);
    }
  }
  http.end();
}

// ================= GAS =================
void bacaGas() {
  int gas = analogRead(GAS_PIN);

  // DARURAT
  if (gas >= gasDarurat && !gasTriggered) {
    gasTriggered = true;
    servoByGas = true;

    Serial.println("!!! GAS DARURAT !!!");

    servo.write(SERVO_OPEN_ANGLE);
    servoOpen = true;
    digitalWrite(BUZZER_PIN, HIGH);

    kirimGasDarurat();
  }

  // NORMAL KEMBALI
  if (gas <= gasNormal && gasTriggered) {
    gasTriggered = false;
    servoByGas = false;

    digitalWrite(BUZZER_PIN, LOW);
    servo.write(SERVO_CLOSE_ANGLE);
    servoOpen = false;

    Serial.println("Gas normal kembali");
  }
}

// ================= RFID =================
void bacaRFID() {
  if (gasTriggered || servoOpen || rfidBusy) return;

  if (!rfid.PICC_IsNewCardPresent()) return;
  if (!rfid.PICC_ReadCardSerial()) return;

  rfidBusy = true;

  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();

  Serial.print("RFID: ");
  Serial.println(uid);

  digitalWrite(BUZZER_PIN, HIGH);
  delay(150);
  digitalWrite(BUZZER_PIN, LOW);

  servo.write(SERVO_OPEN_ANGLE);
  servoOpen = true;
  servoTimer = millis();

  kirimRFID(uid);

  rfid.PICC_HaltA();
  rfidBusy = false;
}

// ================= SERVO =================
void kontrolServo() {
  if (servoByGas) return;

  if (servoOpen && millis() - servoTimer >= SERVO_OPEN_TIME) {
    servo.write(SERVO_CLOSE_ANGLE);
    servoOpen = false;
  }
}

// ================= ULTRASONIK =================
float bacaUltrasonik() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  long durasi = pulseIn(ECHO_PIN, HIGH, 30000);
  if (durasi == 0) return -1;

  return durasi * 0.034 / 2;
}

// ================= SERIAL =================
void tampilSerial() {
  if (millis() - serialTimer >= SERIAL_INTERVAL) {
    serialTimer = millis();

    int gas = analogRead(GAS_PIN);
    float jarak = bacaUltrasonik();

    Serial.print("Gas: ");
    Serial.print(gas);
    Serial.print(" | Jarak: ");
    Serial.print(jarak);
    Serial.println(" cm");

    // Sampah penuh (<50 cm)
    if (jarak > 0 && jarak < 50 && !gasTriggered) {
      for (int i = 0; i < 3; i++) {
        digitalWrite(BUZZER_PIN, HIGH);
        delay(100);
        digitalWrite(BUZZER_PIN, LOW);
        delay(100);
      }
    }
  }
}

// ================= SENSOR HTTP =================
void kirimSensorPeriodik() {
  if (millis() - sensorTimer >= SENSOR_SEND_TIME) {
    sensorTimer = millis();

    int gas = analogRead(GAS_PIN);
    float jarak = bacaUltrasonik();

    if (WiFi.status() != WL_CONNECTED) return;

    HTTPClient http;
    WiFiClient client;

    http.begin(client, String(server) + "/sensor");
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String data = "gas=" + String(gas) + "&jarak=" + String(jarak);
    http.POST(data);
    http.end();
  }
}

// ================= HTTP RFID =================
void kirimRFID(String rfidCode) {
  HTTPClient http;
  WiFiClient client;

  http.begin(client, String(server) + "/rfid");
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  http.POST("rfid=" + rfidCode);
  http.end();
}

// ================= HTTP GAS =================
void kirimGasDarurat() {
  HTTPClient http;
  WiFiClient client;

  http.begin(client, String(server) + "/gas-darurat");
  http.POST("");
  http.end();
}