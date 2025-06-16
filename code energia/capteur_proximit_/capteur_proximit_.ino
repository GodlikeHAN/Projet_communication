const int broche_capteur = A7;          // Broche analogique PD0
const int buzzerPin = 31;          // Broche numérique PF4

const int READINGS = 5;            // Nombre de lectures pour lisser la mesure
const float MARGIN = 1.0;          // Marge pour éviter les basculements trop sensibles

const float CRITICAL_LIMIT = 10.0; // Seuil critique (cm) : trop proche -> alarme
const float WARNING_LIMIT = 30.0;  // Seuil avertissement (cm)

const int tableSize = 8;           // Taille des tableaux de conversion
int sensorTable[tableSize] = {2581, 1965, 1574, 1318, 1104, 963, 889, 813};
float distanceTable[tableSize] = {10,   15,   20,   25,   30,  35,  40,  50};

// Variables pour la gestion du bip d'avertissement
unsigned long previousMillis = 0; 
const unsigned long beepDuration = 200;     // Durée d'un bip
const unsigned long beepCycle = 1000;       // Durée totale d'un cycle (bip + pause)
bool isBeeping = false;

float estimateDistance(int sensorValue) {
  for (int i = 0; i < tableSize - 1; i++) {
    if (sensorValue >= sensorTable[i + 1] && sensorValue <= sensorTable[i]) {
      float ratio = (float)(sensorValue - sensorTable[i + 1]) / (sensorTable[i] - sensorTable[i + 1]);
      return distanceTable[i + 1] + ratio * (distanceTable[i] - distanceTable[i + 1]);
    }
  }
  if (sensorValue > sensorTable[0]) return distanceTable[0];
  if (sensorValue < sensorTable[tableSize - 1]) return distanceTable[tableSize - 1];
  return -1; // Valeur invalide
}

void setup() {
  Serial.begin(9600);
  analogReadResolution(12);         // Résolution de 12 bits
  pinMode(buzzerPin, OUTPUT);       // Configure le buzzer en sortie
}

void loop() {
  // Lecture et moyenne du capteur
  int sensorValue = 0;
  for (int i = 0; i < READINGS; i++) {
    sensorValue += analogRead(broche_capteur);
    delay(2); // Pause courte pour stabiliser
  }
  sensorValue /= READINGS;

  float distanceCm = estimateDistance(sensorValue); // Conversion en cm

  // Affichage des données
  Serial.print("Distance: ");
  Serial.print(distanceCm);
  Serial.println(" cm");

  // Temps actuel
  unsigned long currentMillis = millis();

  // === Cas 1 : distance urgence ===
  if (distanceCm > 0 && distanceCm < (CRITICAL_LIMIT + MARGIN)) {
    tone(buzzerPin, 2000);         // Bip continu
    Serial.println(">>URGENCE");
    isBeeping = false;                     
  }

  // === Cas 2 : distance avertissement ===
  else if (distanceCm >= (CRITICAL_LIMIT + MARGIN) && distanceCm <= (WARNING_LIMIT - MARGIN)) {
    if (currentMillis - previousMillis >= beepCycle) {
      previousMillis = currentMillis;
      tone(buzzerPin, 1000);       // Bip d'avertissement
      isBeeping = true;
    } else if (isBeeping && currentMillis - previousMillis >= beepDuration) {
      noTone(buzzerPin);           // Arrêt du bip
      isBeeping = false;
    }
    Serial.println(">>AVERTISSEMENT");
  }

  // === Cas 3 : bonne distance ===
  else if (distanceCm > (WARNING_LIMIT + MARGIN)) {
    noTone(buzzerPin);             // Pas d'alarme
    isBeeping = false;
    Serial.println(">>OK");
  }

  delay(200);
}
