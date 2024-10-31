class UserCali:
    def __init__(self, _id,_head, _arm, _back, _leg):
        #set profile int
        self.id     = int(_id)
        self.head   = int(_head)
        self.arm    = int(_arm)
        self.back   = int(_back)
        self.leg    = int(_leg)
        
        self.time = 0
        self.imgCali = ""

def find_userCali_by_id(array, _id):
    for data in array:
        if data.id == _id:
            return data
    
    add_object(array, _id, 999, 999, 999, 999)
    data = find_userCali_by_id(array, _id)
    return data

def add_object(array, _id, _head, _arm, _back, _leg):
    array.append(UserCali(_id, _head, _arm, _back, _leg))
    print("makeUserCali")


def remove_object_by_id(array, _id):
    for data in array:
        if data.id == _id:
            acc = data.id
            array.remove(data)
            print("delete account id "+str(acc)+" from calibrate complete")
            return True  # ลบวัตถุสำเร็จ
    return False  # หากไม่พบวัตถุที่ตรงกับ ID