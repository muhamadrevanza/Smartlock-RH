/*
    HTTP over TLS (HTTPS) example sketch

    This example demonstrates how to use
    WiFiClientSecure class to access HTTPS API.
    We fetch and display the status of
    esp8266/Arduino project continuous integration
    build.

    Created by Ivan Grokhotkov, 2015.
    This example is in public domain.
*/

#include <ESP8266WiFi.h>
#include <WiFiManager.h>
#include <ArduinoJson.h>
#include <NTPClient.h>
#include <WiFiUdp.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Adafruit_Fingerprint.h>
#include <SoftwareSerial.h>
#include <ESP8266WebServer.h>
#include <ElegantOTA.h>
#include <ESP8266HTTPClient.h>
#include <BearSSLHelpers.h> // BearSSL for secure HTTPS connection



ESP8266WebServer server(80);
SoftwareSerial serial(D7,D6);

LiquidCrystal_I2C lcd(0x27,16,2);

String ssid = "internet bukitasam"; // tempat untuk mengganti username sesuai dengan hotspot/wifi yang disekitar.
String pass = "bukitasam24"; // tempat untuk mengganti password sesuai dengan hotspot/wifi yang disekitar.

String server_name = "https://amdsultanp.serv00.net/absensi-fingerprint/";

const int pin_kunci = D5;
unsigned long nyala_kunci = 0;
unsigned long now = 0;
int id_fingerprint = 0;
bool hapus_finger = false;
bool tambah_finger = false;
uint8_t id;

unsigned long layar_mati = 0;
bool layar_hidup = true;

Adafruit_Fingerprint finger = Adafruit_Fingerprint(&serial);
String wifi_ip;
String fp_id = " ";

static const char home_root[] PROGMEM = R"=====(
 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fingerprint ID Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">

    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
        <div id="step1">
            <h2 class="text-2xl font-bold mb-4 text-center">Input Fingerprint ID</h2>
            <form id="fingerprintForm" class="space-y-4">
                <div>
                    <input type="number" id="fingerprint_id" name="fingerprint_id" class="block w-full p-2 border border-gray-300 rounded-md" required>
                </div>
                <button type="button" onclick="nextStep()" class="w-full bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600">Next</button><br><br>
                <button type="button" onclick="hapus_id()" class="w-full bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600">Hapus ID</button>
            </form>
        </div>

        <div id="step2" class="hidden">
            <h2 id="step2-title" class="text-2xl font-bold mb-4 text-center">Mengecek ID Fingerprint..</h2>
            <p id="message" class="text-center text-gray-700"></p>
            <div id="nameInput" class="hidden space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name" name="name" class="block w-full p-2 border border-gray-300 rounded-md" required>
                </div>
                <button type="button" onclick="confirmFingerprint()" class="w-full bg-green-500 text-white p-2 rounded-md hover:bg-green-600">Confirm</button>
            </div>
            <button type="button" id="retryButton" onclick="resetForm()" class="hidden w-full bg-red-500 text-white p-2 rounded-md hover:bg-red-600">Batal</button><br><br>
            <button type="button" id="continueButton" class="hidden w-full bg-yellow-500 text-white p-2 rounded-md hover:bg-yellow-600" onclick="continueToNextPage()">Lanjutkan</button>
        </div>
    </div>

    <script>
        function nextStep() {
            var fingerprint_id = document.getElementById('fingerprint_id').value;
            if (fingerprint_id === '') {
                alert('Please enter a Fingerprint ID');
                return;
            }

            // Switch to step 2
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');

            // Check fingerprint ID with AJAX
            fetch(`cek_id?id=${fingerprint_id}`)
                .then(response => response.text())
                .then(data => {
                    var messageElem = document.getElementById('message');
                    var nameInputDiv = document.getElementById('nameInput');
                    var retryButton = document.getElementById('retryButton');
                    var continueButton = document.getElementById('continueButton');
                    
                    if (data.trim() === 'kosong') {
                        document.getElementById('step2-title').innerText = 'ID tidak terdaftar';
                        messageElem.innerText = 'Masukan nama untuk ID fingerprint.';
                        nameInputDiv.classList.remove('hidden');
                        retryButton.classList.add('hidden');
                        continueButton.classList.add('hidden');
                    } else {
                        document.getElementById('step2-title').innerText = 'ID terdaftar';
                        messageElem.innerText = `ID telah terdaftar dengan nama ${data}.`;
                        retryButton.classList.remove('hidden');
                        continueButton.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    alert('Error checking Fingerprint ID');
                    console.error(error);
                });
        }

        function confirmFingerprint() {
            var fingerprint_id = document.getElementById('fingerprint_id').value;
            var name = document.getElementById('name').value;

            if (name === '') {
                alert('Please enter a name');
                return;
            }

            // Send the name and ID to the server
            fetch(`tambah_peserta?nama=${name}&id=${fingerprint_id}`)
                .then(response => response.text())
                .then(data => {
                    alert('Fingerprint ID and Name successfully registered!');
                    resetForm();
                })
                .catch(error => {
                    alert('Error registering Fingerprint ID');
                    console.error(error);
                });
        }
        function hapus_id() {
            var fingerprint_id = document.getElementById('fingerprint_id').value;
            fetch(`hapus_id?id=${fingerprint_id}`)
                .then(response => response.text())
                .then(data => {
                    alert('Fingerprint ID deleted');
                    resetForm();
                })
                .catch(error => {
                    alert('Error registering Fingerprint ID');
                    console.error(error);
                });
        }

        function resetForm() {
            document.getElementById('fingerprintForm').reset();
            document.getElementById('step1').classList.remove('hidden');
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('nameInput').classList.add('hidden');
            document.getElementById('retryButton').classList.add('hidden');
            document.getElementById('continueButton').classList.add('hidden');
            document.getElementById('message').innerText = '';
            document.getElementById('step2-title').innerText = 'Mengecek ID Fingerprint..';
        }

        function continueToNextPage() {
            var fingerprint_id = document.getElementById('fingerprint_id').value;
            window.location.href = `/lanjut?id=${fingerprint_id}`;
        }
    </script>

</body>
</html>


)=====";

String urlEncode(const String &src) {
    String encoded = "";
    char c;
    for (int i = 0; i < src.length(); i++) {
        c = src.charAt(i);
        if (isalnum(c) || c == '-' || c == '_' || c == '.' || c == '~') {
            encoded += c; // karakter yang aman
        } else {
            encoded += '%'; // karakter yang tidak aman
            encoded += String(c, HEX);
            encoded += String(c, HEX).length() == 1 ? "0" : ""; // tambahkan nol di depan jika perlu
        }
    }
    return encoded;
}
// Function to handle form submission
void handleRoot() {
  server.send(200, "text/html", FPSTR(home_root));
}

// Function to handle checking the fingerprint ID
void handleCheckId() {
  if (!server.hasArg("id")) {
    server.send(400, "text/plain", "ID Fingerprint tidak ditemukan.");
    return;
  }

  String fingerprintId = server.arg("id");
  String checkUrl = server_name + "cek_id.php?fingerprint_id=" + fingerprintId;

  HTTPClient http;
  BearSSL::WiFiClientSecure client;
  client.setInsecure(); // Skip certificate validation for demo purposes
  
  http.begin(client, checkUrl);

  int httpCode = http.GET();
  if (httpCode > 0) {
    String payload = http.getString();
    Serial.println(payload);
    server.send(200,"text/plain",payload);
  } else {
    server.send(500, "text/plain", "Gagal menghubungi server.");
  }
  http.end();
}

String pos_fingerprint_id() {
    String checkUrl = server_name + "checklog.php?fingerprint_id=" + fp_id;
    checkUrl.trim();
    HTTPClient http;
    BearSSL::WiFiClientSecure client;
    client.setInsecure(); // Skip certificate validation for demo purposes
    Serial.println(checkUrl);
    http.begin(client, checkUrl);
    String reply;
    int httpCode = http.GET();
    if (httpCode > 0) {
        reply = http.getString();
        Serial.println("response : " + reply);
        
        // Mengambil hanya baris pertama
        int lineEndIndex = reply.indexOf('\n');
        if (lineEndIndex != -1) {
            reply = reply.substring(0, lineEndIndex);
        }
    } else {
        reply = "ERROR";
    }
    http.end();
    return reply;
}


// Function to confirm adding a participant
void handleConfirm() {
  if (!server.hasArg("nama") || !server.hasArg("id")) {
    server.send(400, "text/plain", "Parameter tidak lengkap.");
    return;
  }
  
  String nama = server.arg("nama");
  nama.replace(" ","+");
  String fingerprintId = server.arg("id");
  id_fingerprint = fingerprintId.toInt();
  tambah_finger = true;
  String addUrl = server_name + "tambah_peserta.php?nama=" + nama + "&id=" + fingerprintId;

  HTTPClient http;
  BearSSL::WiFiClientSecure client;
  client.setInsecure(); // Skip certificate validation for demo purposes

  http.begin(client, addUrl);
  int httpCode = http.GET();
  if (httpCode > 0) {
    String payload = http.getString();
    Serial.println(payload);
    server.send(200, "text/plain", payload);
  } else {
    server.send(500, "text/plain", "Gagal menghubungi server.");
  }
  http.end();
}
void selesai(){
  if (!server.hasArg("id")) {
    server.send(400, "text/plain", "Parameter tidak lengkap.");
    return;
  }
  id_fingerprint = server.arg("id").toInt();
  tambah_finger = true;
  String content  = "<html><script>alert('Fingerprint ID successfully registered!');</script>ok</html>";
  server.send(200,"text/html",content);
}
void lcdshow(int c,int r,String m){
  lcd.setCursor(c,r);
  lcd.print(m);
}
void setup() {
  Serial.begin(115200);
  finger.begin(57600);
  Serial.println();
  lcd.init();
  lcd.backlight();
 
  Serial.println("Begin ok!");
  delay(500);
  lcdshow(0,0,"WIFI SETUP..");
  WiFi.mode(WIFI_STA);
  String mac = WiFi.macAddress();
  WiFiManager wm;
    bool res;
    res = wm.autoConnect("absensi","password"); // password protected ap

    if(!res) {
        Serial.println("Failed to connect");
        // ESP.restart();
    } 
    else {
        //if you get here you have connected to the WiFi    
        Serial.println("connected...yeey :)");
    }
  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
  lcd.setCursor(0,0);
  wifi_ip = WiFi.localIP().toString();
  lcd.print(wifi_ip);
  delay(3000);
  server.on("/hapus_id",[](){
    id_fingerprint = server.arg("id").toInt();
    server.send(200,"text/plain","OK");
    hapus_finger = true;
  });
  server.on("/", handleRoot);
  server.on("/cek_id", handleCheckId);
  server.on("/tambah_peserta", handleConfirm);
  server.on("/lanjut",selesai);
  ElegantOTA.begin(&server); 
  server.begin();
  pinMode(pin_kunci,OUTPUT);
  Serial.print("Waiting for NTP time sync: ");
  lcdshow(0,0,"FINGERPRNT CHECK");
  
  delay(5);
  if (finger.verifyPassword()) {
    Serial.println("Found fingerprint sensor!");
  } else {
    Serial.println("Did not find fingerprint sensor :(");
    lcdshow(0,1,"ERROR");
    while (1) { delay(1); }
  }
  finger.getTemplateCount();
  Serial.print("Sensor contains "); Serial.print(finger.templateCount); Serial.println(" templates");
  lcd.clear();
  layar_mati = millis();
  
  
}


uint8_t getFingerprintID() {
  uint8_t p = finger.getImage();
  switch (p) {
    case FINGERPRINT_OK:
      Serial.println("Image taken");
      fp_id = " ";
      break;
    case FINGERPRINT_NOFINGER:
      Serial.println("No finger detected");
      fp_id = " ";
      return p;
    case FINGERPRINT_PACKETRECIEVEERR:
      Serial.println("Communication error");
      return p;
    case FINGERPRINT_IMAGEFAIL:
      Serial.println("Imaging error");
      return p;
    default:
      Serial.println("Unknown error");
      return p;
  }

  // OK success!

  p = finger.image2Tz();
  switch (p) {
    case FINGERPRINT_OK:
      Serial.println("Image converted");
      break;
    case FINGERPRINT_IMAGEMESS:
      Serial.println("Image too messy");
      fp_id = " ";
      return p;
    case FINGERPRINT_PACKETRECIEVEERR:
      Serial.println("Communication error");
      return p;
    case FINGERPRINT_FEATUREFAIL:
      Serial.println("Could not find fingerprint features");
      fp_id = " ";
      return p;
    case FINGERPRINT_INVALIDIMAGE:
      Serial.println("Could not find fingerprint features");
      fp_id = " ";
      return p;
    default:
      Serial.println("Unknown error");
      return p;
  }
  
  // OK converted!
  p = finger.fingerFastSearch();
  if (p == FINGERPRINT_OK) {
    Serial.println("Found a print match!");
  } else if (p == FINGERPRINT_PACKETRECIEVEERR) {
    Serial.println("Communication error");
    return p;
  } else if (p == FINGERPRINT_NOTFOUND) {
    Serial.println("Did not find a match");
    fp_id = "TIDAK TERDAFTAR";
    return p;
  } else {
    Serial.println("Unknown error");
    return p;
  }   
  
  // found a match!
  Serial.print("Found ID #"); Serial.print(finger.fingerID); 
  Serial.print(" with confidence of "); Serial.println(finger.confidence); 
 fp_id = String(finger.fingerID);
  lcd.clear();
  return finger.fingerID;
}

// returns -1 if failed, otherwise returns ID #
int getFingerprintIDez() {
  uint8_t p = finger.getImage();
  if (p != FINGERPRINT_OK)  return -1;

  p = finger.image2Tz();
  if (p != FINGERPRINT_OK)  return -1;

  p = finger.fingerFastSearch();
  if (p != FINGERPRINT_OK)  return -1;
  
  // found a match!
  Serial.print("Found ID #"); Serial.print(finger.fingerID); 
  Serial.print(" with confidence of "); Serial.println(finger.confidence);
  return finger.fingerID; 
}

uint8_t getFingerprintEnroll() {
  lcd.clear();
  int p = -1;
  Serial.print("Waiting for valid finger to enroll as #"); Serial.println(id);
  while (p != FINGERPRINT_OK) {
    p = finger.getImage();
    switch (p) {
    case FINGERPRINT_OK:
      Serial.println("Image taken");
      lcdshow(0,0,"SUKSES..     ");
      break;
    case FINGERPRINT_NOFINGER:
      Serial.print(".");
      lcdshow(0,0,"WAITING..    ");
      break;
    case FINGERPRINT_PACKETRECIEVEERR:
      Serial.println("Communication error");
      lcdshow(0,0,"ERROR..      ");
      break;
    case FINGERPRINT_IMAGEFAIL:
      Serial.println("Imaging error");
      lcdshow(0,0,"ULANGI LAGI..");
      break;
    default:
      Serial.println("Unknown error");
      break;
    }
  }

  // OK success!

  p = finger.image2Tz(1);
  switch (p) {
    case FINGERPRINT_OK:
      Serial.println("Image converted");
      break;
    case FINGERPRINT_IMAGEMESS:
      Serial.println("Image too messy");
      lcdshow(0,0,"ULANGI LAGI..     ");
      return p;
    case FINGERPRINT_PACKETRECIEVEERR:
      Serial.println("Communication error");
      return p;
    case FINGERPRINT_FEATUREFAIL:
      Serial.println("Could not find fingerprint features");
      return p;
    case FINGERPRINT_INVALIDIMAGE:
      Serial.println("Could not find fingerprint features");
      return p;
    default:
      Serial.println("Unknown error");
      return p;
  }

  Serial.println("Remove finger");
  lcdshow(0,0,"ANGKAT JARI..  ");
  p = 0;
  while (p != FINGERPRINT_NOFINGER) {
    p = finger.getImage();
  }
  Serial.print("ID "); Serial.println(id);
  p = -1;
  Serial.println("Place same finger again");
  lcdshow(0,0,"TEMPELKAN JARI..");
  while (p != FINGERPRINT_OK) {
    p = finger.getImage();
    switch (p) {
    case FINGERPRINT_OK:
      Serial.println("Image taken");
      break;
    case FINGERPRINT_NOFINGER:
      Serial.print(".");
      break;
    case FINGERPRINT_PACKETRECIEVEERR:
      Serial.println("Communication error");
      break;
    case FINGERPRINT_IMAGEFAIL:
      Serial.println("Imaging error");
      break;
    default:
      Serial.println("Unknown error");
      break;
    }
  }

  // OK success!

  p = finger.image2Tz(2);
  switch (p) {
    case FINGERPRINT_OK:
      Serial.println("Image converted");
      lcdshow(0,0,"SUKSES..        ");
      break;
    case FINGERPRINT_IMAGEMESS:
      Serial.println("Image too messy");
      return p;
    case FINGERPRINT_PACKETRECIEVEERR:
      Serial.println("Communication error");
      return p;
    case FINGERPRINT_FEATUREFAIL:
      Serial.println("Could not find fingerprint features");
      return p;
    case FINGERPRINT_INVALIDIMAGE:
      Serial.println("Could not find fingerprint features");
      return p;
    default:
      Serial.println("Unknown error");
      return p;
  }

  // OK converted!
  Serial.print("Creating model for #");  Serial.println(id);

  p = finger.createModel();
  if (p == FINGERPRINT_OK) {
    Serial.println("Prints matched!");
  } else if (p == FINGERPRINT_PACKETRECIEVEERR) {
    Serial.println("Communication error");
    return p;
  } else if (p == FINGERPRINT_ENROLLMISMATCH) {
    Serial.println("Fingerprints did not match");
    return p;
  } else {
    Serial.println("Unknown error");
    return p;
  }

  Serial.print("ID "); Serial.println(id);
  p = finger.storeModel(id);
  if (p == FINGERPRINT_OK) {
    Serial.println("Stored!");
  } else if (p == FINGERPRINT_PACKETRECIEVEERR) {
    Serial.println("Communication error");
    return p;
  } else if (p == FINGERPRINT_BADLOCATION) {
    Serial.println("Could not store in that location");
    return p;
  } else if (p == FINGERPRINT_FLASHERR) {
    Serial.println("Error writing to flash");
    return p;
  } else {
    Serial.println("Unknown error");
    return p;
  }

  return true;
}
uint8_t deleteFingerprint(uint8_t id) {
  uint8_t p = -1;

  p = finger.deleteModel(id);

  if (p == FINGERPRINT_OK) {
    Serial.println("Deleted!");
  } else if (p == FINGERPRINT_PACKETRECIEVEERR) {
    Serial.println("Communication error");
  } else if (p == FINGERPRINT_BADLOCATION) {
    Serial.println("Could not delete in that location");
  } else if (p == FINGERPRINT_FLASHERR) {
    Serial.println("Error writing to flash");
  } else {
    Serial.print("Unknown error: 0x"); Serial.println(p, HEX);
  }

  return p;
}

void hidupkan_layar(){
  layar_mati = millis();
  layar_hidup = true;
  lcd.backlight();
}
unsigned long baca_fp = 0;
void loop() {
  server.handleClient();
 
  if(millis() - baca_fp >= 1000){
    baca_fp = millis();
    Serial.println(getFingerprintID());
  }
  if(tambah_finger == true){
    hidupkan_layar();
    lcd.clear();
    id = id_fingerprint;
    lcdshow(0,0,"CREATE ID:" + String(id));
    delay(1000);
    
    getFingerprintEnroll();
    tambah_finger = false;
  }
  while(hapus_finger == true){
    hidupkan_layar();
    lcd.clear();
    lcdshow(0,0,"HAPUS ID:" + String(id_fingerprint));
    id = id_fingerprint;
    deleteFingerprint(id);
    delay(500);
    lcd.clear();
    hapus_finger = false;
  }
  if(fp_id == " "){
    lcdshow(0,0,wifi_ip);
    lcdshow(0,1,"----------------");
  }
  else if(fp_id == "TIDAK TERDAFTAR"){
    hidupkan_layar();
    lcdshow(0,1,fp_id);
  }
  else {
    hidupkan_layar();
    lcdshow(0,0,"ID NUMBER:" + fp_id);
    nyala_kunci = millis();
    digitalWrite(pin_kunci,HIGH);
    lcdshow(0,1,pos_fingerprint_id());
    
    delay(1000);
    fp_id = " ";
  }
  if(millis() - layar_mati >= 20000){
    if(layar_hidup == true){
      layar_hidup = false;
      lcd.noBacklight();
    }
  }
  if(millis() - nyala_kunci >= 3000){
    digitalWrite(pin_kunci,LOW);
  }
  
  
}
