from datetime import datetime
import json
import os
import shutil

from fastapi.responses import JSONResponse
import myTools as mt
import mediapipe as mp
import cv2
import atexit
import db
import user_in_process as user
import user_in_calibrate as userCali
from fastapi import FastAPI, HTTPException 
#pip install fastapi
#pip install uvicorn
#uvicorn api:app --host 0.0.0.0 --port 8000 --reload

mp_pose = mp.solutions.pose
pose = mp_pose.Pose()
userObj = []
userCaliObj = []
app = FastAPI()

@app.get("/aaa/")
async def root():
    #db se
    link = "https://drive.google.com/drive/folders/1MwIg3QPB8JNzWHhZnbzlyw1kvZc5uL-l"
    return {
            "userId": 1,
            "id": 1,
            "title": "Hello **** World",
            "link" : link
        }

@app.post("/test_calibrate_L/")
def test_calibrate_L(input_json: dict):
    required_keys = {"accountID", "imgStr","startCalibrate"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(status_code=422, detail="Missing required keys in JSON")

    img_data = mt.load_img("imgTestL.jpg")
    imgstr = mt.image_to_base64(img_data)
    
    idUserCali = int(input_json["accountID"])
    
    output_json = {
        "id"         : idUserCali,
        "state_no"   : 3,
        "state_name" : "end",
        "angle"     : [153, 19, 101, 97],
        "Ear"       : [0.614728569984436, 0.18122820556163788],
        "Shoulder"  : [0.7042111754417419, 0.29555782675743103],
        "Elbow"     : [0.5928969383239746, 0.44375866651535034],   
        "Hip"       : [0.6785225868225098, 0.5500026941299438],
        "Knee"      : [0.29514530301094055, 0.5796353816986084],
        "Ankle"     : [0.2975139319896698, 0.7577764987945557],
        "is_left"   : True,
        "imgStr"    : imgstr,
        "imgSize"   : [720,1280]#x,y
    }
    return output_json


@app.post("/test_calibrate_R/")
def test_calibrate_R(input_json: dict):
    required_keys = {"accountID", "imgStr","startCalibrate"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(status_code=422, detail="Missing required keys in JSON")

    img_data = mt.load_img("imgTestR.jpg")
    imgstr = mt.image_to_base64(img_data)
    
    
    idUserCali = int(input_json["accountID"])
    
    output_json = {
        "id"         : idUserCali,
        "state_no"   : 3,
        "state_name" : "end",
        "angle"     : [100, 15, 96, 151],
        "Ear"       : [0.38532981276512146, 0.1766664981842041],
        "Shoulder"  : [0.2941663861274719, 0.29644209146499634],
        "Elbow"     : [0.39405500888824463, 0.44373732805252075],   
        "Hip"       : [0.3355177044868469, 0.5498861074447632],
        "Knee"      : [0.6981144547462463, 0.5690451860427856],
        "Ankle"     : [0.7007626295089722, 0.7538790106773376],
        "is_left"   : False,
        "imgStr"    : imgstr,
        "imgSize"   : [720,1280]#x,y
    }
    return output_json


@app.post("/process_calibrate/")
def process_calibrate(input_json: dict):
    required_keys = {"accountID", "imgStr","startCalibrate"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(status_code=422, detail="Missing required keys in JSON")
    
    connDB()
    currUserCali = userCali.find_userCali_by_id(userCaliObj, int(input_json["accountID"]))
    is_start = True
    startCalibrate = input_json["startCalibrate"]

    if(startCalibrate == "T"):
        is_start = True
    else:
        is_start = False

    idUserCali = currUserCali.id
    stateNo = 9
    stateName = "calibrate error"
    angleCali = None
    pointEar        = None
    pointShoulder   = None
    pointElbow      = None
    pointHip        = None
    pointKnee       = None
    pointAnkle      = None
    is_left = None
    is_user_sitting = False
    img64Resize = None
    imgSize = None
    
    
    try:#process img
        img64 = input_json["imgStr"]
        image = mt.base64_to_CVimg(img64)
        image = mt.resize_with_padding(image)
        result = pose.process(image)
        stateNo = 0
        stateName = "Find user"
    except:
        print("MediaPipe Error")
        
    try:#find Person
        keypoints = result.pose_landmarks.landmark
        is_user_sitting = True
        stateNo = 1
        stateName = "wait"
    except:
        is_user_sitting = False
    
    if(is_user_sitting == True and is_start):
        try:#side check
            
            h, w, c = image.shape
            l_Shoulder = keypoints[11]
            r_Shoulder = keypoints[12]
            if(l_Shoulder.visibility > r_Shoulder.visibility):
                numKey = [0,7,9,11,13,23,25,27,31]
                is_left = True
            else:
                numKey = [0,8,10,12,14,24,26,28,32]
                is_left = False
            #Nose,Ear,Mouth,Shoulder,Elbow,Hip,Knee,Ankle,Foot
        except:
            print("LR CK error")
            
        try:
            # get angles
            pNose       = keypoints[numKey[0]]
            pEar        = keypoints[numKey[1]]
            pMouth      = keypoints[numKey[2]]
            pShoulder   = keypoints[numKey[3]]
            pElbow      = keypoints[numKey[4]]
            pHip        = keypoints[numKey[5]]
            pKnee       = keypoints[numKey[6]]
            pAnkle      = keypoints[numKey[7]]
            pFoot       = keypoints[numKey[8]]

            Nose        = (pNose.x      * w  ,  pNose.y       *h)
            Ear         = (pEar.x       * w  ,  pEar.y        *h)
            Mouth       = (pMouth.x     * w  ,  pMouth.y      *h)
            Shoulder    = (pShoulder.x  * w  ,  pShoulder.y   *h)
            Elbow       = (pElbow.x     * w  ,  pElbow.y      *h)
            Hip         = (pHip.x       * w  ,  pHip.y        *h)
            Knee        = (pKnee.x      * w  ,  pKnee.y       *h)
            Ankle       = (pAnkle.x     * w  ,  pAnkle.y      *h)
            Foot        = (pFoot.x      * w  ,  pFoot.y       *h)

            angleHead   = mt.calculate_angle(Ear,   Shoulder,   Hip     )
            angleArm    = mt.calculate_angle(Elbow, Shoulder,   Hip     )
            angleBack   = mt.calculate_angle(Knee,  Hip,        Shoulder)
            angleLeg    = mt.calculate_angle(Ankle, Knee,       Hip     )

            print("Head = "+str(angleHead))
            print("Arm  = "+str(angleArm ))
            print("Back = "+str(angleBack))
            print("Leg  = "+str(angleLeg ))
            
            try:
                if(currUserCali.head != 999):
                    currUserCali.head = (currUserCali.head + angleHead)/2
                else:
                    currUserCali.head = angleHead
                    
                if(currUserCali.arm != 999):
                    currUserCali.arm = (currUserCali.arm + angleArm)/2
                else:
                    currUserCali.arm = angleArm
                    
                if(currUserCali.back != 999):
                    currUserCali.back = (currUserCali.back + angleBack)/2
                else:
                    currUserCali.back = angleBack
                    
                if(currUserCali.leg != 999):
                    currUserCali.leg = (currUserCali.leg + angleLeg)/2
                else:
                    currUserCali.leg = angleLeg
                
                currUserCali.time = currUserCali.time+1
            except:
                print("Cali memory error")
            stateNo     = 2
            stateName   = "start"
            
        except:
            print("Cali Degree Set error")
    
    if(currUserCali.time >= 5):
        stateNo     = 3
        stateName   = "end"
        
        acc_id      = int(input_json["accountID"])
        head        = int(currUserCali.head)
        arm         = int(currUserCali.arm)
        back        = int(currUserCali.back)
        leg         = int(currUserCali.leg)
        imgPath     = str(currUserCali.id) + ".jpg"
        pointEar        = [pEar.x     ,pEar.y     ]
        pointShoulder   = [pShoulder.x,pShoulder.y]
        pointElbow      = [pElbow.x   ,pElbow.y   ]
        pointHip        = [pHip.x     ,pHip.y     ]
        pointKnee       = [pKnee.x    ,pKnee.y    ]
        pointAnkle      = [pAnkle.x   ,pAnkle.y   ]
        angleCali = {head,arm,back,leg}
        
        
        img64Resize = mt.image_to_base64(image)
        imgSize = [w,h]
        #บันทึกข้อมูล
        print(str(db.update_profileAxis(connection, acc_id, head, arm, back, leg, imgPath)))
        db.update_calibratedDT(connection, acc_id)
        try:
            # กำหนด path
            dir_path = os.path.join("images", "profile")

            # สร้างไดเรกทอรีถ้ายังไม่มี
            os.makedirs(dir_path, exist_ok=True)

            # path ที่จะใช้บันทึกรูปภาพ
            full_path = os.path.join(dir_path, str(idUserCali) + ".jpg")
            # บันทึกรูปภาพ
            cv2.imwrite(full_path, image)
        except:
            print("save Profile Image Error")
            
            
            
        userCali.remove_object_by_id(userCaliObj, int(input_json["accountID"]))
    output_json = {
        "id": idUserCali,
        "state_no"   :stateNo,
        "state_name" :stateName,
        "angle"     : angleCali,
        "Ear"       : pointEar,
        "Shoulder"  : pointShoulder,
        "Elbow"     : pointElbow,   
        "Hip"       : pointHip,
        "Knee"      : pointKnee,
        "Ankle"     : pointAnkle,
        "is_left"   : is_left,
        "imgStr"    : img64Resize,
        "imgSize"   : imgSize#x,y
    }
    return output_json

@app.post("/set_calibrate/")
def set_calibrate(input_json: dict):
    required_keys = {"accountID", "imgStr", "newPoint", "is_left"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(status_code=422, detail="Missing required keys in JSON")
    
    connDB()
    acc_id = int(input_json["accountID"])
    newPoint = json.loads(input_json["newPoint"])  # Decode JSON string to dictionary

    # Extract points and ensure they are lists of floats
    earPoint = [float(coord) for coord in newPoint["Ear"]]
    shoulderPoint = [float(coord) for coord in newPoint["Shoulder"]]
    elbowPoint = [float(coord) for coord in newPoint["Elbow"]]
    hipPoint = [float(coord) for coord in newPoint["Hip"]]
    kneePoint = [float(coord) for coord in newPoint["Knee"]]
    anklePoint = [float(coord) for coord in newPoint["Ankle"]]



    
    is_left = bool(input_json["is_left"])
    image64 = str(input_json["imgStr"])
    imgPath = str(input_json["accountID"]) + ".jpg"
    res = True

    # Find angle
    try:
        # Decode base64 image back to OpenCV image
        image = mt.base64_to_CVimg(image64)
        h, w, c = image.shape
        
        # Find position (x, y) of each point
        Ear = (earPoint[0] * w, earPoint[1] * h)
        Shoulder = (shoulderPoint[0] * w, shoulderPoint[1] * h)
        Elbow = (elbowPoint[0] * w, elbowPoint[1] * h)
        Hip = (hipPoint[0] * w, hipPoint[1] * h)
        Knee = (kneePoint[0] * w, kneePoint[1] * h)
        Ankle = (anklePoint[0] * w, anklePoint[1] * h)
        

        #find angle
        angleHead   = mt.calculate_angle(Ear,   Shoulder,   Hip     )
        angleArm    = mt.calculate_angle(Elbow, Shoulder,   Hip     )
        angleBack   = mt.calculate_angle(Knee,  Hip,        Shoulder)
        angleLeg    = mt.calculate_angle(Ankle, Knee,       Hip     )
        
        print("find angle process Success")
    except:
        print("find angle process Error")
        res = False
    
    
    try:#update DB
        res = db.update_profileAxis(connection, acc_id, angleHead, angleArm, angleBack, angleLeg, imgPath)
        db.update_calibratedDT(connection, acc_id)
    except:
        print("update DB Error")
        res = False

    try:#blur
        #blur1 = keypoints[numKey[0]]
        #b1x, b1y = int(blur1.x * w), int(blur1.y * h)
        #blur2 = keypoints[numKey[1]]
        #b2x, b2y = int(blur2.x * w), int(blur2.y * h)
        #blurX = int((b1x+b2x)/2)
        #blurY = int((b1y+b2y)/2)
        Ear = (earPoint[0] * w, earPoint[1] * h)
        wEar = int(earPoint[0] * w)
        hEar = int(earPoint[1] * h)
        cv2.circle(image, (wEar,hEar), 70, (161, 161, 161), -1)
    except:
        print("Blur error")


    try:#save img
        # กำหนด path
        dir_path = os.path.join("images", "profile")

        # สร้างไดเรกทอรีถ้ายังไม่มี
        os.makedirs(dir_path, exist_ok=True)

        # path ที่จะใช้บันทึกรูปภาพ
        full_path = os.path.join(dir_path, imgPath)
        # บันทึกรูปภาพ
        cv2.imwrite(full_path, image)
    except:
        print("save Profile Image Error")
        res = False
    
    print("acc_id       "+ str(acc_id))
    #print("newPoint     "+ str(newPoint ))
    print("earPoint     "+ str(Ear      ))
    print("shoulderPoint"+ str(Shoulder ))
    print("elbowPoint   "+ str(Elbow    ))
    print("hipPoint     "+ str(Hip      ))
    print("kneePoint    "+ str(Knee     ))
    print("anklePoint   "+ str(Ankle    ))
    print("angleH       "+ str(angleHead ))
    print("angleA       "+ str(angleArm  ))
    print("angleB       "+ str(angleBack ))
    print("angleL       "+ str(angleLeg  ))
    
    output_json = {
        "success" : res
    }
    return output_json

#Process Detect
@app.post("/process_detect/")
def process_detect(input_json: dict):
    required_keys = {"accountID", "imgStr"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(status_code=422, detail="Missing required keys in JSON")

    #connDB()
    
    currUser = None
    try:
        currUser = getUser(int(input_json["accountID"]))
        if not currUser:
            raise Exception("User not found")  # ในกรณีที่ getUser ไม่คืนค่า
    except Exception as e:
        print(f"Error: {str(e)}")
        raise HTTPException(status_code=404, detail="User not found")

    alarm, point = procMediaPipe(input_json["imgStr"], currUser)
    data = point

    output_json = {
        "id": int(input_json["accountID"]),
        "alarm": alarm,
        "data": data
    }
    return output_json

def procMediaPipe(img64,currUser):
    #setting
    #connDB()
    current_detect_time = datetime.strptime(mt.getDateTime(), "%Y-%m-%d %H:%M:%S")
    
    userNotFoundMessage = "User not found"
    headMessage         = "head"
    armMessage          = "arm"
    backMessage         = "back"
    legMessage          = "leg"
    sitLimitMessage     = "outSitLimit"
    timeOutMessage      = "Time Out"

    limitAngle = {
        "default": 15,
        "head": 15,
        "arm": 45,
        "back": 15,
        "leg": 15
    }
    print(str(limitAngle))
    limitTimeAngle      = 1
    timeOut             = 10
    desiredVisibility   = 0.8
    
    incorrectPoint = []
    alarmMessage = []
    alarmKey=0
        # 0 = normal, 1 = detectSitting, 2 = outLimitSitting, 3 = 1 and 2
    
    
    
        
    try:#process img
        image = mt.base64_to_CVimg(img64)
        image = mt.resize_with_padding(image)
        result = pose.process(image)
    except:
        print("MediaPipe Error")
        
        
    try:#find Person
        keypoints = result.pose_landmarks.landmark
        is_user_sitting = True
        currUser.last_find_time = current_detect_time
    except:
        is_user_sitting = False
        if(currUser.start_sitting_time is not None):
            sitting_time = (current_detect_time - currUser.start_sitting_time).total_seconds() / 60.0
            currUser.sitDuration = currUser.sitDuration + sitting_time
            
        currUser.start_sitting_time = None
        currUser.last_notification_time_out = None
        print("sitDuration = "+str(currUser.sitDuration))
        
        if(currUser.last_find_time is not None):
            elapsed_time_out = (current_detect_time - currUser.last_find_time).total_seconds() / 60.0
            print(elapsed_time_out)
            if(elapsed_time_out > timeOut):
                print("Time Out")
                alarmMessage.append(timeOutMessage)
            else:
                print("Not Found Person")
                alarmMessage.append(userNotFoundMessage)
        else:
            print("Not Found Person")
            alarmMessage.append(userNotFoundMessage)
        return alarmKey,alarmMessage
    
    
    try:
        #set start_sitting_time
        if is_user_sitting: 
            if currUser.start_sitting_time is None:
                currUser.start_sitting_time = current_detect_time
                print("set start_sitting_time")
                
        #check elapsed_sitting_time
        if currUser.start_sitting_time is not None:
            elapsed_sitting_time = (current_detect_time - currUser.start_sitting_time).total_seconds() / 60.0
            print("elapsed_sitting_time = "+str(elapsed_sitting_time))
            print("sitLimit = "+str(currUser.sitLimit))
            try:
                #set last_notification_time_out
                last_notification_time_out = (current_detect_time - currUser.last_notification_time_out).total_seconds() / 60.0
            except:
                last_notification_time_out = None
            print("last_notification_time = "+str(last_notification_time_out))
            print("sitLimitFreq = "+str(currUser.sitLimitFreq))
            if elapsed_sitting_time > currUser.sitLimit:
                if currUser.last_notification_time_out is None: 
                    #Sit Over Limit
                    currUser.amountSitOverLimit = currUser.amountSitOverLimit + 1
                    currUser.last_notification_time_out = current_detect_time
                    print("last_notification_time = none")
                    alarmMessage.append(sitLimitMessage)
                    alarmKey=alarmKey+2
                else:
                    if (current_detect_time - currUser.last_notification_time_out).total_seconds() / 60.0 > currUser.sitLimitFreq:
                        
                        currUser.last_notification_time_out = current_detect_time
                        #print(current_detect_time.strftime("%Y-%m-%d %H:%M:%S"))
                        
                        #Sit Over Limit
                        alarmMessage.append(sitLimitMessage)
                        alarmKey=alarmKey+2
    except:
        print("sitLimit Error")
    
    
    try:#side check
        h, w, c = image.shape
        l_Shoulder = keypoints[11]
        r_Shoulder = keypoints[12]
        if(l_Shoulder.visibility > r_Shoulder.visibility):
            numKey = [0,7,9,11,13,23,25,27,31]
            needNumKey = [0,7,9,11,13,23,25,27]
        else:
            numKey = [0,8,10,12,14,24,26,28,32]
            needNumKey = [0,8,10,12,14,24,26,28]
        #Nose,Ear,Mouth,Shoulder,Elbow,Hip,Knee,Ankle,Foot
    except:
        print("LR CK error")
    
    
    try:#full body check
        for idx in needNumKey:
            point = keypoints[idx]
            print("Visibility "+str(desiredVisibility)+" / "+str(point.visibility))
            if(point.visibility<desiredVisibility):
                print("Not detecting all points in the body")
                is_user_sitting = False
                if(currUser.start_sitting_time is not None):
                    sitting_time = (current_detect_time - currUser.start_sitting_time).total_seconds() / 60.0
                    currUser.sitDuration = currUser.sitDuration + sitting_time

                currUser.start_sitting_time = None
                currUser.last_notification_time_out = None
                print("sitDuration = "+str(currUser.sitDuration))

                if(currUser.last_find_time is not None):
                    elapsed_time_out = (current_detect_time - currUser.last_find_time).total_seconds() / 60.0
                    print(elapsed_time_out)
                    if(elapsed_time_out > timeOut):
                        print("Time Out")
                        alarmMessage.append(timeOutMessage)
                    else:
                        print("Not Found Person")
                        alarmMessage.append(userNotFoundMessage)
                else:
                    print("Not Found Person")
                    alarmMessage.append(userNotFoundMessage)
                return alarmKey,alarmMessage
    except:
        print("full body check error")
    
    
    
    try:#blur
        blur1 = keypoints[numKey[0]]
        b1x, b1y = int(blur1.x * w), int(blur1.y * h)
        blur2 = keypoints[numKey[1]]
        b2x, b2y = int(blur2.x * w), int(blur2.y * h)
        blurX = int((b1x+b2x)/2)
        blurY = int((b1y+b2y)/2)
        cv2.circle(image, (blurX, blurY), 120, (161, 161, 161), -1)
    except:
        print("Blur error")
        
        
    try:# get angles
        pNose       = keypoints[numKey[0]]
        pEar        = keypoints[numKey[1]]
        pMouth      = keypoints[numKey[2]]
        pShoulder   = keypoints[numKey[3]]
        pElbow      = keypoints[numKey[4]]
        pHip        = keypoints[numKey[5]]
        pKnee       = keypoints[numKey[6]]
        pAnkle      = keypoints[numKey[7]]
        pFoot       = keypoints[numKey[8]]
        
        Nose        = (pNose.x      * w  ,  pNose.y       *h)
        Ear         = (pEar.x       * w  ,  pEar.y        *h)
        Mouth       = (pMouth.x     * w  ,  pMouth.y      *h)
        Shoulder    = (pShoulder.x  * w  ,  pShoulder.y   *h)
        Elbow       = (pElbow.x     * w  ,  pElbow.y      *h)
        Hip         = (pHip.x       * w  ,  pHip.y        *h)
        Knee        = (pKnee.x      * w  ,  pKnee.y       *h)
        Ankle       = (pAnkle.x     * w  ,  pAnkle.y      *h)
        Foot        = (pFoot.x      * w  ,  pFoot.y       *h)
        
        angleHead   = mt.calculate_angle(Ear,   Shoulder,   Hip     )
        angleArm    = mt.calculate_angle(Elbow, Shoulder,   Hip     )
        angleBack   = mt.calculate_angle(Knee,  Hip,        Shoulder)
        angleLeg    = mt.calculate_angle(Ankle, Knee,       Hip     )
        
        #print("Head = "+str(angleHead))
        #print("Arm  = "+str(angleArm ))
        #print("Back = "+str(angleBack))
        #print("Leg  = "+str(angleLeg ))
    except:
        print("degreeSet error")
            
            
    try:# ตรวจสอบค่าใน ProfileAxis และเพิ่มข้อความแจ้งเตือนใน alarmMessage
        detected = False
        
        profHead= currUser.head
        profArm = currUser.arm
        profBack= currUser.back
        profLeg = currUser.leg

        diffHead = round(profHead - angleHead, 2)
        diffArm  = round(profArm  - angleArm , 2)
        diffBack = round(profBack - angleBack, 2) 
        diffLeg  = round(profLeg  - angleLeg , 2)
        
        abs_diffHead=abs(diffHead)
        abs_diffArm =abs(diffArm )
        abs_diffBack=abs(diffBack)
        abs_diffLeg =abs(diffLeg )
        
        if abs_diffHead > limitAngle["head"]:
            if(currUser.outLimitHead >= limitTimeAngle-1):
                currUser.detectHead = currUser.detectHead + 1 
                alarmMessage.append(headMessage)
                incorrectPoint.append('H')
                detected = True
                currUser.outLimitHead = 0
            else:
                currUser.outLimitHead = currUser.outLimitHead + 1
                
        if abs_diffArm  > limitAngle["arm"]:
            if(currUser.outLimitArm >= limitTimeAngle-1):
                currUser.detectArm = currUser.detectArm + 1
                alarmMessage.append(armMessage)
                incorrectPoint.append('A')
                detected = True
                currUser.outLimitArm = 0
            else:
                currUser.outLimitArm = currUser.outLimitArm + 1
                
        if abs_diffBack > limitAngle["back"]:
            if(currUser.outLimitBack >= limitTimeAngle-1):
                currUser.detectBack = currUser.detectBack + 1
                alarmMessage.append(backMessage)
                incorrectPoint.append('B')
                detected = True
                currUser.outLimitBack = 0
            else:
                currUser.outLimitBack = currUser.outLimitBack + 1
                
        if abs_diffLeg  > limitAngle["leg"]:
            if(currUser.outLimitLeg >= limitTimeAngle-1):
                currUser.detectLeg = currUser.detectLeg + 1
                alarmMessage.append(legMessage)
                incorrectPoint.append('L')
                detected = True
                currUser.outLimitLeg = 0
            else:
                currUser.outLimitLeg = currUser.outLimitLeg + 1
                

        if(detected):
            currUser.detectAmount = currUser.detectAmount + 1
            alarmKey=alarmKey + 1
        
        print("Head = "+str(profHead)+' - '+str(angleHead)  +' = '+str(diffHead))
        print("Arm  = "+str(profArm )+' - '+str(angleArm )  +' = '+str(diffArm ))
        print("Back = "+str(profBack)+' - '+str(angleBack)  +' = '+str(diffBack))
        print("Leg  = "+str(profLeg )+' - '+str(angleLeg )  +' = '+str(diffLeg ))

        print(alarmMessage)
    except Exception as e:
        print("alarm message error:", e)
        
    if(detected):
        try:#draw
            for idx in numKey:
                point = keypoints[idx]
                cx, cy = int(point.x * w), int(point.y * h)
                cv2.circle(image, (cx, cy), 14, (0, 255, 0), -1)
        except:
            print("drawPoints error")
    
    
        try:
            # ดึงเวลาปัจจุบัน
            current_time = datetime.now()
            yyyymmdd_date = current_time.strftime("%Y%m%d")
            hhmmss_time = current_time.strftime("%H%M%S")

            # กำหนด path
            dir_path = os.path.join("images", yyyymmdd_date, str(currUser.id))

            # สร้างไดเรกทอรีถ้ายังไม่มี
            os.makedirs(dir_path, exist_ok=True)

            # path ที่จะใช้บันทึกรูปภาพ
            full_path = os.path.join(dir_path, hhmmss_time + ".jpg")
            # บันทึกรูปภาพ
            cv2.imwrite(full_path, image)

            print(f"save image to {full_path} complete")
        except Exception as e:
            print(f"save image error : {e}")
            
        try:
            evidence = hhmmss_time+".jpg"
            incorrectPoints = ','.join(incorrectPoint)
            insert_return = db.add_detected(connection,currUser.id,current_time,incorrectPoints,evidence)
            print(insert_return)
        except Exception as e:
            print(f"insert db error : {e}")
            
    return alarmKey,alarmMessage

@app.post("/end_detect/")
def end_detect(input_json: dict):
    required_keys = {"accountID"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(status_code=422, detail="Missing required keys in JSON")
    #database
    connDB()
    accID = int(input_json["accountID"])
    currUser = user.find_object_by_id(userObj, accID)
    
    status = "not find user in process"
    
    if(currUser is not None):
        #found user in process
        print("found user in process")
        try:
            if(currUser.last_find_time is not None):
                if(currUser.start_sitting_time is not None):
                    sitting_time = (currUser.last_find_time - currUser.start_sitting_time).total_seconds() / 60.0
                    currUser.sitDuration = currUser.sitDuration + sitting_time
                    print(str(currUser.sitDuration))
                    status = "ok"
            else:
                status = "ok"
        except:
            print("error to end detect")
            status = "error to end detect"
            
        if(currUser.last_find_time is None):
            detectDate = None
        else:
            dateLast = currUser.last_find_time
            detectDate = dateLast.strftime("%Y-%m-%d")
        
        
        
        #set DailyHistory
        acc_id              = currUser.id
        detectDate          = detectDate
        detectAmount        = int(currUser.detectAmount)      
        head                = int(currUser.detectHead)        
        arm                 = int(currUser.detectArm)         
        back                = int(currUser.detectBack)        
        leg                 = int(currUser.detectLeg)         
        sitDuration         = int(currUser.sitDuration)       
        amountSitOverLimit  = int(currUser.amountSitOverLimit)
        sitLimitOnDay       = int(currUser.sitLimit)
        a = db.add_dailyreport(connection,acc_id, detectDate,detectAmount,head,arm,back,leg,sitDuration,amountSitOverLimit,sitLimitOnDay)
        print(a)
        
        #delete user in process
        user.remove_object_by_id(userObj, accID)
    else:
        print("not found user in process")
    output_json = {
        "status": status
    }
    return output_json 

def getUser(accountID):
    connDB()
    accountID = int(accountID)
    if None!=user.find_object_by_id(userObj,accountID):
        print("Find User Data")
        return user.find_object_by_id(userObj,accountID)
    else:
        accID, detectFreq, sitLimit, sitLimitAlarmFreq, lastDetectDT = db.find_account(connection,accountID)
        headDegree, armDegree, backDegree, legDegree = db.get_profileAxis(connection,accountID)
        user.add_object(
            userObj,
            int(accID),
            detectFreq,
            sitLimit,
            sitLimitAlarmFreq,
            lastDetectDT,
            headDegree, 
            armDegree, 
            backDegree, 
            legDegree
            )
        print("Add User Data")
        return user.find_object_by_id(userObj,accountID)

@app.post("/daily_history_data_img/")
def daily_history_data_img(input_json: dict):
    required_keys = {"accountID", "date"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(status_code=422, detail="Missing required keys in JSON")
    
    connDB()
    try:
        # Fetch daily history metrics
        daily_report = db.get_dailyHistory(connection, int(input_json["accountID"]), input_json["date"])
        if(daily_report==None):
            print("daily_report==None")
            return None
        detectAmount = int(daily_report["detectAmount"])
        sitDuration = int(daily_report["sitDuration"])
        amountSitOverLimit = int(daily_report["amountSitOverLimit"])
        head = int(daily_report["head"])
        arm = int(daily_report["arm"])
        back = int(daily_report["back"])
        leg = int(daily_report["leg"])
        
        # Fetch detection records with images
        detectList = []
        detect_records = db.get_detectHistory_Img(connection, int(input_json["accountID"]), input_json["date"])
        if(detect_records==None):
            print("detect_records==None")
            
        else:
            for row in detect_records:
                detectID = int(row["detectedID"])
                time = str(row["detectDT"].time())
                incorrectPoint = str(row["incorrectPoint"])
                imgPath = str(row["evidence"])

                try:
                    print("loading:", imgPath)
                    Date = input_json["date"]
                    date_time = datetime.strptime(Date, "%Y-%m-%d")
                    yyyymmdd_date = date_time.strftime("%Y%m%d")
                    imgPath = "images/"+yyyymmdd_date+"/"+str(input_json["accountID"])+"/"+imgPath
                    img_data = mt.load_img(imgPath)
                    imgStr = mt.image_to_base64(img_data)
                except Exception as e:
                    print(f"Error loading image from {imgPath}: {e}")
                    imgStr = None

                detectedHead = 'H' in incorrectPoint
                detectedArm = 'A' in incorrectPoint
                detectedBack = 'B' in incorrectPoint
                detectedLeg = 'L' in incorrectPoint

                detect = {
                    "detectID": detectID,
                    "time": time,
                    "detectImg": imgStr,
                    "detectedHead": detectedHead,
                    "detectedArm": detectedArm,
                    "detectedBack": detectedBack,
                    "detectedLeg": detectedLeg
                }

                detectList.append(detect)
        
        output_json = {
            "detectAmount": detectAmount,
            "sitDuration": sitDuration,
            "amountSitOverLimit": amountSitOverLimit,
            "head": head,
            "arm": arm,
            "back": back,
            "leg": leg,
            "detectList": detectList
        }
        
        return output_json
    
    except Exception as ex:
        print(f"An error occurred: {ex}")
        raise HTTPException(status_code=500, detail="Internal server error")

@app.post("/month_history/")
def month_history(input_json: dict):
    required_keys = {"accountID", "monthYear"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(status_code=422, detail="Missing required keys in JSON")
    
    connDB()
    monthYear = input_json["monthYear"]
    month , year = monthYear.split("-")
    
    
    detect_img_records = db.get_monthHistory(connection,int(input_json["accountID"]), year,month )
     
        
    
    #amount detect table
    totalMonthDetect = db.get_totalMonthDetect(connection,int(input_json["accountID"]),year,month)
    

    output = {
        "detect_img_records" : detect_img_records,
        "totalMonthDetect" : totalMonthDetect
    }
    return output

@app.post("/login/")
def process_login(input_json: dict):
    required_keys = {"email", "name"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(
            status_code=422, 
            detail="Missing required keys in JSON")
    connDB()    
    try:
        acc = db.get_Login(connection,input_json["email"])
        accountID = int(acc["accountID"])
        imgProfileAxis = None
        detectFreq  = acc["detectFreq"]
        sitLimit    = acc["sitLimit"]
        sitLimitFreq= acc["sitLimitAlarmFreq"]
        newNotification = bool(acc["newNotification"])
        try:
            profile_img_path = db.get_profileAxis_img(connection,accountID)
            img_path = "images/profile/"+profile_img_path
            img_data = mt.load_img(img_path)
            imgProfileAxis = mt.image_to_base64(img_data)
        except:
            print("Missing imgProfileAxis.png")
        
        settingChoice = db.get_settingChoice(connection,1)
        detectFreqChoice    = []
        sitLimitChoice      = []
        sitLimitFreqChoice  = []
        for Choice in settingChoice:
            if(Choice['groupID']==1):
                detectFreqChoice.append(int(Choice['item']))
            elif(Choice['groupID']==2):
                sitLimitChoice.append(int(Choice['item']))
            elif(Choice['groupID']==3):
                sitLimitFreqChoice.append(int(Choice['item']))
        
        # update login DT
        db.update_lastLoginDT(connection, int(acc["accountID"]))

        login_output = {
            "accountID"         : accountID,
            "imgProfileAxis"    : imgProfileAxis,
            "detectFreq"        : detectFreq,
            "detectFreqChoice"  : detectFreqChoice,
            "sitLimit"          : sitLimit,
            "sitLimitChoice"    : sitLimitChoice,
            "sitLimitFreq"      : sitLimitFreq,
            "sitLimitFreqChoice": sitLimitFreqChoice,
            "newNotification"   : newNotification
        }
    except:
        
        # add user
        acc = db.add_user(connection, input_json["email"], input_json["name"])
        db.add_profileAxis(connection, int(acc["accountID"]))

        accountID = int(acc["accountID"])
        imgProfileAxis = None
        detectFreq  = acc["detectFreq"]
        sitLimit    = acc["sitLimit"]
        sitLimitFreq= acc["sitLimitAlarmFreq"]
        newNotification = bool(acc["newNotification"])
        try:
            profile_img_path = db.get_profileAxis_img(connection,accountID)
            img_path = "images/profile/"+profile_img_path
            img_data = mt.load_img(img_path)
            imgProfileAxis = mt.image_to_base64(img_data)
        except:
            print("Missing imgProfileAxis.png")
        
        settingChoice = db.get_settingChoice(connection,1)
        detectFreqChoice    = []
        sitLimitChoice      = []
        sitLimitFreqChoice  = []
        for Choice in settingChoice:
            if(Choice['groupID']==1):
                detectFreqChoice.append(int(Choice['item']))
            elif(Choice['groupID']==2):
                sitLimitChoice.append(int(Choice['item']))
            elif(Choice['groupID']==3):
                sitLimitFreqChoice.append(int(Choice['item']))
        
        login_output = {
            "accountID"         : accountID,
            "imgProfileAxis"    : imgProfileAxis,
            "detectFreq"        : detectFreq,
            "detectFreqChoice"  : detectFreqChoice,
            "sitLimit"          : sitLimit,
            "sitLimitChoice"    : sitLimitChoice,
            "sitLimitFreq"      : sitLimitFreq,
            "sitLimitFreqChoice": sitLimitFreqChoice,
            "newNotification"   : newNotification
        }
    return login_output

@app.post("/update_setting/")
def process_update_setting(input_json: dict):
    required_keys = {"accountID", "detectFreq","sitLimit","sitLimitFreq"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(
            status_code=422, 
            detail="Missing required keys in JSON")
    connDB()  
    try:
        
        accountID       = int(input_json["accountID"])
        detectFreq      = input_json["detectFreq"]
        sitLimit        = input_json["sitLimit"]
        sitLimitFreq    = input_json["sitLimitFreq"]
        
        update_setting_output=db.update_setting(connection,accountID,detectFreq,sitLimit,sitLimitFreq)
        
    except:
        update_setting_output = False
    return update_setting_output

@app.post("/pre_delete_by_date_and_accId/")
def pre_delete_by_date_and_accId(input_json: dict):
    # ตรวจสอบว่ามีคีย์ที่จำเป็นทั้งหมดใน JSON หรือไม่
    required_keys = {"accId", "startDate", "endDate"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(
            status_code=422, 
            detail="Missing required keys in JSON"
        )
    connDB()
    
    startDate = input_json["startDate"]
    endDate = input_json["endDate"]
    accID = int(input_json["accId"])  

    result = db.pre_delete_user(connection, accID, startDate, endDate)
    
    output = {
        "amount": result
    }
    return output

@app.post("/pre_delete_by_date/")
def pre_delete_by_date(input_json: dict):
    required_keys = {"startDate", "endDate"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(
            status_code=422, 
            detail="Missing required keys in JSON")
    
    try:
        startDate       = input_json["startDate"]
        endDate         = input_json["endDate"]
        base_path = "images"
        
        total_size = 0
        
        try:
            start_date = datetime.strptime(startDate, "%Y-%m-%d")
            end_date = datetime.strptime(endDate, "%Y-%m-%d")
        except:
            print("str to date error")

        # ตรวจสอบว่าโฟลเดอร์หลักมีอยู่หรือไม่
        if not os.path.exists(base_path):
            print(f"โฟลเดอร์หลัก '{base_path}' ไม่พบ")
            return
        print(f"โฟลเดอร์หลัก '{base_path}' พบ")

            # ตรวจสอบว่าเป็นไดเร็กทอรีหรือไม่
        if os.path.isdir(base_path):
            # วนลูปผ่านโฟลเดอร์วันที่ในแต่ละ AccountID
            for date_folder in os.listdir(base_path):
                date_path = os.path.join(base_path, date_folder)

                # ตรวจสอบว่าเป็นไดเร็กทอรีและชื่อโฟลเดอร์ตรงตามรูปแบบวันที่หรือไม่
                if os.path.isdir(date_path) and date_folder != "profile" :
                    try:
                        folder_date = datetime.strptime(date_folder, "%Y%m%d")
                        print(f"โฟลเดอร์ '{folder_date}' พบ")
                        # ตรวจสอบว่าโฟลเดอร์อยู่ในช่วงวันที่กำหนดหรือไม่
                        if start_date <= folder_date <= end_date:
                            total_size += get_folder_size(date_path)
                            print(str(total_size))
                    except ValueError:
                        # ข้ามโฟลเดอร์ที่ชื่อไม่ตรงกับรูปแบบวันที่
                        pass
                        

    except:
        print("pre_delete_by_date ERROR")
    return format_size(total_size)#ตัวอย่างข้อมูล round(size, 2), unit

@app.post("/delete_img_by_date/")
def process_delete_img_by_date(input_json: dict):
    required_keys = {"startDate", "endDate"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(
            status_code=422, 
            detail="Missing required keys in JSON")
    connDB()    
    try:
        startDate       = input_json["startDate"]
        endDate         = input_json["endDate"]
        base_path = "images"
        try:
            start_date = datetime.strptime(startDate, "%Y-%m-%d")
            end_date = datetime.strptime(endDate, "%Y-%m-%d")
        except:
            print("str to date error")

        # ตรวจสอบว่าโฟลเดอร์หลักมีอยู่หรือไม่
        if not os.path.exists(base_path):
            print(f"โฟลเดอร์หลัก '{base_path}' ไม่พบ")
            return
        print(f"โฟลเดอร์หลัก '{base_path}' พบ")

            # ตรวจสอบว่าเป็นไดเร็กทอรีหรือไม่
        if os.path.isdir(base_path):
            # วนลูปผ่านโฟลเดอร์วันที่ในแต่ละ AccountID
            for date_folder in os.listdir(base_path):
                date_path = os.path.join(base_path, date_folder)

                # ตรวจสอบว่าเป็นไดเร็กทอรีและชื่อโฟลเดอร์ตรงตามรูปแบบวันที่หรือไม่
                if os.path.isdir(date_path) and date_folder != "profile" :
                    try:
                        folder_date = datetime.strptime(date_folder, "%Y%m%d")
                        print(f"โฟลเดอร์ '{folder_date}' พบ")
                        # ตรวจสอบว่าโฟลเดอร์อยู่ในช่วงวันที่กำหนดหรือไม่
                        if start_date <= folder_date <= end_date:
                            shutil.rmtree(date_path)
                            print(f"ลบโฟลเดอร์ '{date_path}' สำเร็จ")
                    except ValueError:
                        # ข้ามโฟลเดอร์ที่ชื่อไม่ตรงกับรูปแบบวันที่
                        pass
                        
            delete_img_by_date = True
    
    except:
        delete_img_by_date = False
    try:
        delete_img_by_date = db.delete_detected_all(connection, startDate, endDate)
    except:
        delete_img_by_date = False
    return delete_img_by_date

@app.post("/delete_img_by_date_and_accId/")
def process_delete_img_by_date_and_accId(input_json: dict):
    # ตรวจสอบว่ามีคีย์ที่จำเป็นทั้งหมดใน JSON หรือไม่
    required_keys = {"accId", "startDate", "endDate"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(
            status_code=422, 
            detail="Missing required keys in JSON"
        )
    connDB()   
    try:
        startDate = input_json["startDate"]
        endDate = input_json["endDate"]
        accId = str(input_json["accId"])  # รับค่า accId จาก input_json
        
        db.delete_detected_by_user(connection, int(input_json["accId"]), startDate, endDate)
        
        base_path = "images"

        try:
            # แปลงวันที่จากสตริงเป็น datetime
            start_date = datetime.strptime(startDate, "%Y-%m-%d")
            end_date = datetime.strptime(endDate, "%Y-%m-%d")
        except ValueError:
            print("Error converting string to date")
            return False

        # ตรวจสอบว่าโฟลเดอร์หลักมีอยู่หรือไม่
        if not os.path.exists(base_path):
            print(f"Folder '{base_path}' not found")
            return False
        
        print(f"Folder '{base_path}' found")

        # ตรวจสอบว่าเป็นไดเร็กทอรีหรือไม่
        if os.path.isdir(base_path):
            # วนลูปผ่านโฟลเดอร์วันที่
            for date_folder in os.listdir(base_path):
                date_path = os.path.join(base_path, date_folder)

                # ตรวจสอบว่าเป็นไดเร็กทอรีและชื่อโฟลเดอร์ตรงตามรูปแบบวันที่หรือไม่
                if os.path.isdir(date_path):
                    try:
                        folder_date = datetime.strptime(date_folder, "%Y%m%d")
                        print(f"Folder '{folder_date}' found")
                        # ตรวจสอบว่าโฟลเดอร์อยู่ในช่วงวันที่กำหนดหรือไม่
                        if start_date <= folder_date <= end_date:
                            # ตรวจสอบว่ามีโฟลเดอร์ accId อยู่ในโฟลเดอร์วันที่หรือไม่
                            acc_path = os.path.join(date_path, accId)
                            if os.path.isdir(acc_path):
                                shutil.rmtree(acc_path)
                                print(f"Successfully deleted folder '{acc_path}'")
                    except ValueError:
                        # ข้ามโฟลเดอร์ที่ชื่อไม่ตรงกับรูปแบบวันที่
                        pass
                        
        delete_img_by_date = True

    except Exception as e:
        # พิมพ์ข้อผิดพลาดที่เกิดขึ้น
        print(f"An error occurred: {e}")
        delete_img_by_date = False

    return delete_img_by_date

@app.post("/get_detectDT_max_min/")
def get_detectDT_max_min(input_json: dict):
    accId = input_json.get("accountID", None)  # ใช้ get เพื่อดึงค่าหรือ None ถ้าไม่มี key นี้
    connDB()
    if accId:  # ตรวจสอบว่ามีค่า accId หรือไม่
        print("Get max min DetectDT from id = "+str(accId))
        result = db.get_detect_max_min_DT_id(connection, int(accId))
    else:
        
        result = db.get_detect_max_min_DT_All(connection)
        
    return {
        "max": result['max_detectDT'],
        "min": result['min_detectDT']
    }
    
@app.get("/get_detectDT_max_min_all/")
def get_detectDT_max_min_all():
    connDB()
    result = db.get_detect_max_min_DT_All(connection)
        
    return {
        "max": result['max_detectDT'],
        "min": result['min_detectDT']
    }

@app.get("/get_images_size/")
async def get_images_size():
    directory = "images"
    
    total_size = 0
    for dirpath, dirnames, filenames in os.walk(directory):
        for filename in filenames:
            filepath = os.path.join(dirpath, filename)
            if os.path.isfile(filepath):
                total_size += os.path.getsize(filepath)

    formatted_size, unit = format_size(total_size)

    return {
            "size": formatted_size,
            "unit": unit
        }

@app.get("/noti/")
async def noti():
    reconnDB()
    res = db.get_noti(connection)
    return JSONResponse(content=res, media_type="application/json; charset=utf-8")

@app.post("/read_notification/")
def read_notification(input_json: dict):
    required_keys = {"accountID"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(
            status_code=422, 
            detail="Missing required keys in JSON")
    reconnDB()
    accId = input_json.get("accountID") 
    
    #db
    res = db.set_userReadNoti(connection,accId)
    return {
        "success": res
    }

@app.post("/newNoti/")
def newNoti(input_json: dict):
    required_keys = {"accountID"}
    if not required_keys.issubset(input_json.keys()):
        raise HTTPException(
            status_code=422,
            detail="Missing required keys in JSON"
        )
    reconnDB()  
    accId = int(input_json["accountID"])
    print("Get newNoti from id = "+str(accId))
    result = db.get_newNoit(connection, int(accId))
    print(str(result))
       
    return {
        "newNoti": result
    }



def format_size(size):
        for unit in ['B', 'KB', 'MB', 'GB', 'TB']:
            if size < 1024:
                return round(size, 2), unit
            size /= 1024

def get_folder_size(folder: str) -> int:
    total_size = 0
    # เดินเข้าไปในโฟลเดอร์และรวมขนาดของไฟล์
    for dirpath, dirnames, filenames in os.walk(folder):
        for f in filenames:
            fp = os.path.join(dirpath, f)
            if os.path.exists(fp):
                total_size += os.path.getsize(fp)
    return total_size

def stopApi():
    db.close_connection(connection)
    print("Connection Close")
    
# ตรวจสอบและเชื่อมต่อใหม่
def connDB():
    global connection  # ระบุว่า connection เป็นตัวแปร global
    if connection.is_connected():
        print("SQL Connection is OK")
    else:
        # เชื่อมต่อใหม่
        connection = db.connect_to_mysql()
        if connection.is_connected():
            print("SQL Reconnection success")
        else:
            print("SQL connection error")

def reconnDB():
    global connection  # ระบุว่า connection เป็นตัวแปร global
    if connection.is_connected():
        connection.close()
        print("SQL Connection Close")
        connection = db.connect_to_mysql()
        if connection.is_connected():
            print("SQL Reconnection success")
        else:
            print("SQL connection error")
    else:
        # เชื่อมต่อใหม่
        connection = db.connect_to_mysql()
        if connection.is_connected():
            print("SQL Reconnection success")
        else:
            print("SQL connection error")

# ปิดการเชื่อมต่อ

atexit.register(stopApi)
# เรียกใช้งานฟังก์ชันเชื่อมต่อ
connection = db.connect_to_mysql()
#connection = None
connDB()
#uvicorn api:app --host 0.0.0.0 --port 8000 --reload
#uvicorn api:app --reload

if __name__ == "__main__":
    os.system("uvicorn api:app --host 0.0.0.0 --port 8000 --reload")