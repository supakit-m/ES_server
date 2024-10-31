import base64
import datetime
import json
import math
import time
import random
import numpy as np

import cv2

def calculate_angle(A, B, C):#A = (0, 50)B = (0, 0)C = (1, 50)
    # คำนวณตำแหน่งของ B จาก A
    x_BA = B[0] - A[0]
    y_BA = B[1] - A[1]
    dAB = math.sqrt(x_BA**2 + y_BA**2)

    # คำนวณตำแหน่งของ B จาก C
    x_BC = B[0] - C[0]
    y_BC = B[1] - C[1]
    dBC = math.sqrt(x_BC**2 + y_BC**2)

    # คำนวณมุม B โดยใช้ความสัมพันธ์ระหว่างเส้น AB และ BC
    angle_B = math.acos((x_BA * x_BC + y_BA * y_BC) / (dAB * dBC))

    # แปลงมุมจากเรเดียนเป็นองศา
    angle_B_degree = round(math.degrees(angle_B), 2)
    

    return angle_B_degree


# โหลดภาพ
def load_img(path_img):
    with open(path_img, 'rb') as image_file:
        return image_file.read()

# แปลงข้อมูล ไฟล์ภาพ เป็น base64
def image_to_base64(image_data):
    encoded_string = base64.b64encode(image_data).decode('utf-8')
    return encoded_string

# แปลงข้อมูล base64 เป็นไฟล์ภาพ
def base64_to_image(base64_string):
    image_data = base64.b64decode(base64_string)
    return image_data

# บันทึกภาพไปยังไฟล์
def save_img(image_data, output_path):
    with open(output_path, 'wb') as output_image:
        output_image.write(image_data)


def base64_to_CVimg(base64_str):
    # ตัดส่วนข้อมูลรหัสเชิงฐาน 64 ที่เป็น header
    base64_str = base64_str.split(",")[-1]

    # แปลงข้อมูลรหัสเชิงฐาน 64 กลับเป็นข้อมูลไบนารี
    img_data = base64.b64decode(base64_str)

    # แปลงข้อมูลไบนารีเป็น numpy array
    img_array = np.frombuffer(img_data, np.uint8)

    # อ่านภาพจาก numpy array ด้วย OpenCV
    img = cv2.imdecode(img_array, cv2.IMREAD_COLOR)

    return img

def resize_with_padding(image, color=(255, 255, 255)):
    """
    Resize image to the fixed size of 720x1280 while keeping the aspect ratio by adding padding.


    :param image: Input image in OpenCV format
    :param color: Padding color in BGR format (default is black)
    :return: Resized and padded image with size 720x1280
    """
    # Fixed target dimensions
    target_w, target_h = 720, 1280


    # Get original dimensions
    h, w, _ = image.shape


    # Compute scaling factor while maintaining aspect ratio
    scale = min(target_w / w, target_h / h)
   
    # Resize the image while keeping the aspect ratio
    new_w = int(w * scale)
    new_h = int(h * scale)
    resized_image = cv2.resize(image, (new_w, new_h))


    # Create a new blank image with the target size and fill with the chosen color
    padded_image = np.full((target_h, target_w, 3), color, dtype=np.uint8)  # Fill with color


    # Center the resized image on the new blank image
    x_offset = (target_w - new_w) // 2
    y_offset = (target_h - new_h) // 2
    padded_image[y_offset:y_offset + new_h, x_offset:x_offset + new_w] = resized_image


    return padded_image



def resizeImg(image):
    image = cv2.resize(image, (720, 1280))
    return image

def getDateTime():
    current_time = datetime.datetime.now()
    formatted_time = current_time.strftime("%Y-%m-%d %H:%M:%S")
    #print("วันที่และเวลาปัจจุบันคือ:", formatted_time)
    #print(formatted_time)
    return formatted_time


#getDateTime()