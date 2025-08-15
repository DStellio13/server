int col[8] = {7,2,A0,4,12,A1,11,A3};
int row[8] = {3,A5,A4,6,A2,5,1,0}; 
int appear[8][8] = {{0,0,0,0,0,0,0,0}, //heart
                    {0,1,0,0,0,0,1,0}, 
                    {1,0,1,0,0,1,0,1},
                    {1,0,0,1,1,0,0,1},
                    {0,1,0,0,0,0,1,0},
                    {0,0,1,0,0,1,0,0},
                    {0,0,0,1,1,0,0,0},
                    {0,0,0,0,0,0,0,0}};                         
void setup() {
  for(int i = 0;i < 8;i++){
    pinMode(row[i],OUTPUT);
    pinMode(col[i],OUTPUT);
    digitalWrite(row[i],LOW);//set rows to high voltage, set columns to low voltage to avoid turning on
    digitalWrite(col[i],HIGH);
  }
}
void loop() {
      draw();
}

void draw(){
  for(int i = 0;i < 8;i++){
    for(int j = 0;j < 8;j++){ 
      if(appear[i][j] == 1){//check row by row and column by column if it is equal to 1
        digitalWrite(col[i],LOW);//start lighting from the position in the array that is 1
        digitalWrite(row[j],HIGH);
        delay(1);
        digitalWrite(row[j],LOW);//turn off LEDs after the position in the array that is 1
        digitalWrite(col[i],HIGH);
        }
      } 
   }
}

