const int relayPin = 6;

void setup() {
  // Set relayPin as an output pin
  pinMode(relayPin, OUTPUT);
}

void loop() {
  // Turn off the relay by setting the pin LOW
  digitalWrite(relayPin, LOW);
  delay(3000);

  // Turn on the relay by setting the pin HIGH
  digitalWrite(relayPin, HIGH);
  delay(3000);
}
