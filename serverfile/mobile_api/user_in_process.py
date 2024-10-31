class Data:
    def __init__(self, _id, _detectFreq, _sitLimit, _sitLimitFreq, _lastDetectDT, _head, _arm, _back, _leg):
        #set
        self.id = _id
        #self.time = _time
        #self.num = _num
        self.detectFreq     = _detectFreq
        self.sitLimit       = _sitLimit
        self.sitLimitFreq   = _sitLimitFreq
        self.head    = _head
        self.arm     = _arm 
        self.back    = _back
        self.leg     = _leg 
        #in process
        self.lastDetectDT   = _lastDetectDT
        self.outLimitHead   = 0
        self.outLimitArm    = 0
        self.outLimitBack   = 0
        self.outLimitLeg    = 0
        self.start_sitting_time         = None #เวลาที่ผู้ใช้เริ่มนั่ง
        self.last_notification_time_out = None 
        self.last_find_time             = None #เวลาสุดท้ายที่พบผู้ใช้
        #count
        self.detectAmount       = 0
        self.detectHead         = 0
        self.detectArm          = 0
        self.detectBack         = 0
        self.detectLeg          = 0
        self.sitDuration        = 0 
        self.amountSitOverLimit = 0

# ฟังก์ชันในการหาวัตถุตาม ID
def find_object_by_id(array, _id):
    for data in array:
        if data.id == _id:
            return data
    return None  # หากไม่พบวัตถุที่ตรงกับ ID

# ฟังก์ชันในการเพิ่มวัตถุใหม่
def add_object(array, _id, _detectFreq, _sitLimit, _sitLimitFreq, _lastDetectDT, _head, _arm, _back, _leg):
    array.append(Data(_id, _detectFreq, _sitLimit, _sitLimitFreq, _lastDetectDT, _head, _arm, _back, _leg))

# ฟังก์ชันในการลบวัตถุตาม ID
def remove_object_by_id(array, _id):
    for data in array:
        if data.id == _id:
            acc = data.id
            array.remove(data)
            print("delete account id "+str(acc)+" from process complete")
            return True  # ลบวัตถุสำเร็จ
    return False  # หากไม่พบวัตถุที่ตรงกับ ID







