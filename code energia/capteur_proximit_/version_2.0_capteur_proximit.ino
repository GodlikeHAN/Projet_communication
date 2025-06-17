/* ---------- broches ---------- */
const int broche_capteur = A7;      // capteur
const int buzzerPin      = 31;      // buzzer

/* ---------- paramètres ---------- */
const int  READINGS = 5;
const float MARGIN  = 1.0;
const float CRITICAL = 10.0;   // cm
const float WARNING  = 30.0;   // cm

const int tableSize = 8;
int   sensorTable [tableSize] = {2581,1965,1574,1318,1104,963,889,813};
float distanceTable[tableSize] = {10,  15,  20,  25,  30, 35, 40, 50};

/* ---------- état ---------- */
bool  remoteForced = false;   // true  => ignore le capteur
bool  remoteOn     = false;   // état souhaité par le web
unsigned long tPrev=0;
bool  isBeeping    = false;
const unsigned long beepDur   = 200;
const unsigned long beepCycle = 1000;

/* ---------- utils ---------- */
float estimateDistance(int v){
  for (int i=0;i<tableSize-1;i++)
    if (v>=sensorTable[i+1] && v<=sensorTable[i]){
      float k=(float)(v-sensorTable[i+1])/(sensorTable[i]-sensorTable[i+1]);
      return distanceTable[i+1]+k*(distanceTable[i]-distanceTable[i+1]);
    }
  if (v>sensorTable[0])             return distanceTable[0];
  if (v<sensorTable[tableSize-1])   return distanceTable[tableSize-1];
  return -1;
}

void setup(){
  Serial.begin(9600);
  analogReadResolution(12);
  pinMode(buzzerPin, OUTPUT);
}

void loop(){
  /* ===== 1. Ordres série ON / OFF / AUTO ===== */
  if (Serial.available()){
    String cmd = Serial.readStringUntil('\n');
    cmd.trim();
    cmd.toUpperCase();

    if (cmd == "ON"){
      remoteForced = true;  remoteOn = true;
      tone(buzzerPin, 2000);
      Serial.println(">>BUZZER_ON");
    }else if (cmd == "OFF"){
      remoteForced = true;  remoteOn = false;
      noTone(buzzerPin);  isBeeping = false;
      Serial.println(">>BUZZER_OFF");
    }else if (cmd == "AUTO"){
      remoteForced = false;          // retour au mode auto
      noTone(buzzerPin);  isBeeping = false;
      Serial.println(">>BUZZER_AUTO");
    }
  }

  /* ===== 2. Lecture capteur + logique auto ===== */
  int raw=0;
  for(int i=0;i<READINGS;i++){ raw+=analogRead(broche_capteur); delay(2); }
  raw /= READINGS;
  float d = estimateDistance(raw);

  Serial.print("Distance: "); Serial.print(d); Serial.println(" cm");

  if (remoteForced){               // web mode -> on applique l'ordre puis on sort
      delay(200);
      return;
  }

  unsigned long now = millis();

  if (d>0 && d<CRITICAL+MARGIN){           // urgence
      tone(buzzerPin, 2000);
      Serial.println(">>URGENCE");
      isBeeping=false;
  }else if (d>=CRITICAL+MARGIN && d<=WARNING-MARGIN){  // avertissement
      if (now-tPrev>=beepCycle){
          tPrev=now; tone(buzzerPin,1000); isBeeping=true;
      }else if(isBeeping && now-tPrev>=beepDur){
          noTone(buzzerPin); isBeeping=false;
      }
      Serial.println(">>AVERTISSEMENT");
  }else{                                   // zone OK
      noTone(buzzerPin); isBeeping=false;
      Serial.println(">>OK");
  }
  delay(200);
}
