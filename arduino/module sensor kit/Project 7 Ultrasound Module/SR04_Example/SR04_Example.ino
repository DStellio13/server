
#include "SR04.h"
#define TRIG_PIN 12
#define ECHO_PIN 11
SR04 sr04 = SR04(ECHO_PIN,TRIG_PIN); // 声明一个SR04类型的对象sr04，并在构造函数中传入Echo和Trig引脚的编号，这样就创建了一个SR04对象，可以通过这个对象来控制超声波传感器并获取测量结果。
long a;

void setup() {
   Serial.begin(9600); //串口设置9600
   delay(1000); 
}

void loop() {
   a=sr04.Distance();
   Serial.print(a);
   Serial.println("cm");
   delay(500);
}
