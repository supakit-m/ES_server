1. วิธีติดตั้ง Web Application
  - นำไฟล์ทั้งหมดในโฟลเดอร์ serverfile ไปวางในโฟลเดอร์ htdocs ของ XAMPP หรือในโฟลเดอร์ที่ตั้งค่าไว้สำหรับใช้งาน Apache
2. วิธีติดตั้ง MySQL
  - เปิดโปรแกรมจัดการ MySQL เช่น phpMyAdmin หรือ MySQL Workbench
  - สร้างฐานข้อมูลใหม่ (Database)
  - นำเข้าไฟล์ mesb_database.sql ไปยังฐานข้อมูลที่สร้างไว้เพื่อทำการติดตั้งโครงสร้างและข้อมูลของฐานข้อมูล
3. วิธีติดตั้ง Python และไลบรารีเพื่อใช้งาน Python API
  - ดาวน์โหลดและติดตั้ง Python เวอร์ชันล่าสุดจากเว็บไซต์ทางการของ Python "https://www.python.org/downloads/"
  - เมื่อติดตั้งเสร็จแล้ว ทดสอบว่า Python ติดตั้งสำเร็จด้วยการเปิด Command Prompt หรือ Terminal แล้วพิมพ์ "python --version"
  - ติดตั้งไลบรารีที่จำเป็นด้วยการเปิด Command Prompt หรือ Terminal แล้วพิมพ์
    - pip install mediapipe==0.10.14
    - pip install opencv-python==4.9.0.80
    - pip install numpy==1.26.3
    - pip install fastapi==0.110.2
    - pip install uvicorn==0.27.0
    - pip install mysql-connector-python==8.4.0
  - ตรวจสอบการติดตั้งไลบรารี ด้วยการเปิด Command Prompt หรือ Terminal แล้วพิมพ์ "pip list"
4. วิธีรัน API ด้วย Python
  - ไปที่ไฟล์ api.py ในเส้นทาง serverfile/mobile_api/api.py
  - เปิดไฟล์ api.py ขึ้นมาในโปรแกรมเขียนโค้ด หรือ Terminal
