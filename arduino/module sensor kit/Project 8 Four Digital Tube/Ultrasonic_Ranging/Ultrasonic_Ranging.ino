// display 1234 
	// select pin for cathode
	int a = 2;
	int b = 3;
	int c = 4;
	int d = 5;
	int e = 6;
	int f = 7;
	int g = 8;
	int dp = 9;
	// select pin for anode
	int d4 = 13;
	int d3 = 12;
	int d2 = 11;
	int d1 = 10;
	// set variable
	long n = 1230;
	int x = 100;
	int del = 55;  // fine adjustment for clock
	int TrigPin =A0;
  int EchoPin =A1;
  int datn;
	void setup()
	{
	  pinMode(d1, OUTPUT);
	  pinMode(d2, OUTPUT);
	  pinMode(d3, OUTPUT);
	  pinMode(d4, OUTPUT);
	  pinMode(a, OUTPUT);
	  pinMode(b, OUTPUT);
	  pinMode(c, OUTPUT);
	  pinMode(d, OUTPUT);
	  pinMode(e, OUTPUT);
	  pinMode(f, OUTPUT);
	  pinMode(g, OUTPUT);
	  pinMode(dp, OUTPUT);
    pinMode(TrigPin,OUTPUT);
    pinMode(EchoPin,INPUT); 
	}
/////////////////////////////////////////////////////////////
void loop()
{
// Display(1, 1);
// Display(2, 2);
// Display(3, 3);
// Display(4, 4);
displaycm();

}
///////////////////////////////////////////////////////////////
void displaycm()
{
  int r=0;
  int q=0;
  int w=0;
  int e=0;
  int l;
  digitalWrite(TrigPin,LOW);
  delayMicroseconds(2);
  digitalWrite(TrigPin,HIGH);
  delayMicroseconds(10);
  digitalWrite(TrigPin,LOW);
  
  datn = pulseIn(EchoPin,HIGH)/58.00;
  //datn=202;
  if(datn>225)
 {datn=225;
  }
  Serial.print("juli=");
  Serial. print(datn);
  Serial. print("cm");
  Serial. println();
  //datn=datn*10;
  //delay(1000);
      for(l=0;l<20;l++)
        {
        r=datn/1000;//千位
        q=(datn/100)%10;//百位
        w=(datn/10)%10;//十位
        e=datn%10;//个位
        Clear();
        Display(1, r);
        Display(2, q);
        Display(3, w);
        Display(4, e);
        }
}






void WeiXuan(unsigned char n)//
{
    switch(n)
     {
	case 1: 
	  digitalWrite(d1,LOW);
 	  digitalWrite(d2, HIGH);
	  digitalWrite(d3, HIGH);
	  digitalWrite(d4, HIGH);   
	 break;
	 case 2: 
	  digitalWrite(d1, HIGH);
 	  digitalWrite(d2, LOW);
	  digitalWrite(d3, HIGH);
	  digitalWrite(d4, HIGH); 
	    break;
	  case 3: 
	    digitalWrite(d1,HIGH);
 	   digitalWrite(d2, HIGH);
	   digitalWrite(d3, LOW);
	   digitalWrite(d4, HIGH); 
	    break;
	  case 4: 
	   digitalWrite(d1, HIGH);
 	   digitalWrite(d2, HIGH);
	   digitalWrite(d3, HIGH);
	   digitalWrite(d4, LOW); 
	    break;
        default :
           digitalWrite(d1, HIGH);
	   digitalWrite(d2, HIGH);
	   digitalWrite(d3, HIGH);
	   digitalWrite(d4, HIGH);
        break;
	  }
}
void Num_0()
{
  digitalWrite(a, HIGH);
  digitalWrite(b, HIGH);
  digitalWrite(c, HIGH);
  digitalWrite(d, HIGH);
  digitalWrite(e, HIGH);
  digitalWrite(f, HIGH);
  digitalWrite(g, LOW);
  digitalWrite(dp,LOW);
}
void Num_1()
{
  digitalWrite(a, LOW);
  digitalWrite(b, HIGH);
  digitalWrite(c, HIGH);
  digitalWrite(d, LOW);
  digitalWrite(e, LOW);
  digitalWrite(f, LOW);
  digitalWrite(g, LOW);
  digitalWrite(dp,LOW);
}
void Num_2()
{
  digitalWrite(a, HIGH);
  digitalWrite(b, HIGH);
  digitalWrite(c, LOW);
  digitalWrite(d, HIGH);
  digitalWrite(e, HIGH);
  digitalWrite(f, LOW);
  digitalWrite(g, HIGH);
  digitalWrite(dp,LOW);
}
void Num_3()
{
  digitalWrite(a, HIGH);
  digitalWrite(b, HIGH);
  digitalWrite(c, HIGH);
  digitalWrite(d, HIGH);
  digitalWrite(e, LOW);
  digitalWrite(f, LOW);
  digitalWrite(g, HIGH);
  digitalWrite(dp,LOW);
}
void Num_4()
{
  digitalWrite(a, LOW);
  digitalWrite(b, HIGH);
  digitalWrite(c, HIGH);
  digitalWrite(d, LOW);
  digitalWrite(e, LOW);
  digitalWrite(f, HIGH);
  digitalWrite(g, HIGH);
  digitalWrite(dp,LOW);
}
void Num_5()
{
  digitalWrite(a, HIGH);
  digitalWrite(b, LOW);
  digitalWrite(c, HIGH);
  digitalWrite(d, HIGH);
  digitalWrite(e, LOW);
  digitalWrite(f, HIGH);
  digitalWrite(g, HIGH);
  digitalWrite(dp,LOW);
}
void Num_6()
{
  digitalWrite(a, HIGH);
  digitalWrite(b, LOW);
  digitalWrite(c, HIGH);
  digitalWrite(d, HIGH);
  digitalWrite(e, HIGH);
  digitalWrite(f, HIGH);
  digitalWrite(g, HIGH);
  digitalWrite(dp,LOW);
}
void Num_7()
{
  digitalWrite(a, HIGH);
  digitalWrite(b, HIGH);
  digitalWrite(c, HIGH);
  digitalWrite(d, LOW);
  digitalWrite(e, LOW);
  digitalWrite(f, LOW);
  digitalWrite(g, LOW);
  digitalWrite(dp,LOW);
}
void Num_8()
{
  digitalWrite(a, HIGH);
  digitalWrite(b, HIGH);
  digitalWrite(c, HIGH);
  digitalWrite(d, HIGH);
  digitalWrite(e, HIGH);
  digitalWrite(f, HIGH);
  digitalWrite(g, HIGH);
  digitalWrite(dp,LOW);
}
void Num_9()
{
  digitalWrite(a, HIGH);
  digitalWrite(b, HIGH);
  digitalWrite(c, HIGH);
  digitalWrite(d, HIGH);
  digitalWrite(e, LOW);
  digitalWrite(f, HIGH);
  digitalWrite(g, HIGH);
  digitalWrite(dp,LOW);
}
void Clear()  // clear the screen
{
  digitalWrite(a, LOW);
  digitalWrite(b, LOW);
  digitalWrite(c, LOW);
  digitalWrite(d, LOW);
  digitalWrite(e, LOW);
  digitalWrite(f, LOW);
  digitalWrite(g, LOW);
  digitalWrite(dp,LOW);
}
void pickNumber(unsigned char n)// select number
{
  switch(n)
  {
   case 0:Num_0();
   break;
   case 1:Num_1();
   break;
   case 2:Num_2();
   break;
   case 3:Num_3();
   break;
   case 4:Num_4();
   break;
   case 5:Num_5();
   break;
   case 6:Num_6();
   break;
   case 7:Num_7();
   break;
   case 8:Num_8();
   break;
   case 9:Num_9();
   break;
   default:Clear();
   break; 
  }
}
void Display(unsigned char x, unsigned char Number)//  take x as coordinate and display number
{
  WeiXuan(x);
  pickNumber(Number);
 delay(1);
 Clear() ; // clear the screen
}
