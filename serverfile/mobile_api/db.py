#MESB
import mysql.connector

#for detect system
def connect_to_mysql():
    try:
        # เชื่อมต่อกับ MySQL database
        connection = mysql.connector.connect(
            host="localhost",
            user="root01",   # ชื่อผู้ใช้ของฐานข้อมูล
            password="1234", # รหัสผ่านของฐานข้อมูล
            database="mesb_database" # ชื่อฐานข้อมูลที่ต้องการเชื่อมต่อ
        )
        print("Database connection completed.")
        return connection
    except mysql.connector.Error as error:
        print("Database connection error MySQL: {}".format(error))
        return None

def fetch_data(connection):
    if connection.is_connected():
        try:
            # สร้าง cursor object เพื่อ execute SQL queries
            cursor = connection.cursor()

            # ตัวอย่าง SQL query
            sql_query = "SELECT * FROM test"

            # execute SQL query
            cursor.execute(sql_query)

            # ดึงข้อมูล
            records = cursor.fetchall()

            # แสดงข้อมูล
            for row in records:
                print(row)

            # ปิด cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("เกิดข้อผิดพลาดในการดึงข้อมูล: {}".format(error))
    else:
        print("ไม่สามารถเชื่อมต่อกับ MySQL ได้")

def close_connection(connection):
    if connection:
        connection.close()
        print("Database connection is closed.")
    else:
        print("There are no database connections that need to be closed.")

def find_account(connection, acc_id):
    acc_id = int(acc_id)
    record = None  # เริ่มต้นค่าให้ record เป็น None
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor()
            # ใช้ parameterized query เพื่อป้องกัน SQL Injection
            sql_query = """
                SELECT `accountID`, `detectFreq`, `sitLimit`, 
                       `sitLimitAlarmFreq`, `lastDetectDT` 
                FROM `member` 
                WHERE `accountID` = %s
            """
            # Execute SQL query โดยใช้ parameterized queries
            cursor.execute(sql_query, (acc_id,))

            # Fetch a single row
            record = cursor.fetchone()

            # Display data if a record is found
            if record:
                accountID, detectFreq, sitLimit, sitLimitAlarmFreq, lastDetectDT = record
            else:
                print("No record found with accountID =", acc_id)

        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
        finally:
            # Ensure that the cursor is closed
            cursor.close()
    else:
        print("Cannot connect to MySQL")

    return record


def get_profileAxis(connection,acc_id):
    acc_id = int(acc_id)
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor()

            # Example SQL query with string formatting
            sql_query = """
                SELECT  `headDegree`, `armDegree`, 
                        `backDegree`, `legDegree`
                FROM `profilesaxis` 
                WHERE `accountID` = %s
            """

            # Execute SQL query
            cursor.execute(sql_query, (acc_id,))

            # Fetch a single row
            record = cursor.fetchone()

            # Display data if a record is found
            if record:
                headDegree, armDegree, backDegree, legDegree = record
            else:
                record = None
                print("No record found with accountID =", acc_id)

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")

    return record

def add_dailyreport(connection,acc_id, detectDate,detectAmount,head,arm,back,leg,sitDuration,amountSitOverLimit,sitLimitOnDay):
    if connection.is_connected():
        
        old_daily = check_dailyHistory(connection, acc_id, detectDate)
        if(old_daily is None):
            #print('insert')
        
            try:
                # Create a cursor object to execute SQL queries
                cursor = connection.cursor()

                # Insert query with ON DUPLICATE KEY UPDATE
                insert_query = """
                    INSERT INTO `dailyreport`(
                        `accountID`, 
                        `detectDate`,
                        `detectAmount`, 
                        `head`, 
                        `arm`, 
                        `back`, 
                        `leg`, 
                        `sitDuration`, 
                        `amountSitOverLimit`, 
                        `sitLimitOnDay`
                    ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                """

                # Execute the insert query
                cursor.execute(insert_query, (acc_id, detectDate,detectAmount,head,arm,back,leg,sitDuration,amountSitOverLimit,sitLimitOnDay))

                # Commit the transaction
                connection.commit()

                # Close cursor
                cursor.close()

                return "Insert daily for "+str(acc_id)+" complete"
            except mysql.connector.Error as error:
                return f"Error inserting data: {error}"
        
        else:
            #print('update')
            try:
                # Create a cursor object to execute SQL queries
                cursor = connection.cursor()
    
                # Insert query with ON DUPLICATE KEY UPDATE
                sql_query = """
                    UPDATE `dailyreport` 
                    SET 
                    `detectAmount`=%s,
                    `head`=%s,
                    `arm`=%s,
                    `back`=%s,
                    `leg`=%s,
                    `sitDuration`=%s,
                    `amountSitOverLimit`=%s,
                    `sitLimitOnDay`=%s 
                    WHERE `reportID`=%s
                """
                detectAmount        = detectAmount  + old_daily["detectAmount"]
                head                = head          + old_daily["head"]
                arm                 = arm           + old_daily["arm"]
                back                = back          + old_daily["back"]
                leg                 = leg           + old_daily["leg"]
                sitDuration         = sitDuration   + old_daily["sitDuration"]
                amountSitOverLimit  = amountSitOverLimit + old_daily["amountSitOverLimit"]
                sitLimitOnDay       = sitLimitOnDay + old_daily["sitLimitOnDay"]
                reportID            = old_daily["reportID"]
                # Execute the insert query
                cursor.execute(sql_query, (detectAmount,head,arm,back,leg,sitDuration,amountSitOverLimit,sitLimitOnDay,reportID))
                
                # Commit the transaction
                connection.commit()
    
                # Close cursor
                cursor.close()
                
                return "Update daily for "+str(acc_id)+" complete"
            except mysql.connector.Error as error:
                return f"Error Updating data: {error}"
    else:
        return "Cannot insert or update detected to MySQL"
    
def check_dailyHistory(connection, acc_id, date):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)  # Use dictionary=True to get results as dictionaries

            # Example SQL query with parameterized queries
            sql_query = """
                SELECT `reportID`,`detectAmount`, 
                    `head`, `arm`, `back`, `leg`, 
                    `sitDuration`, 
                    `amountSitOverLimit`,
                    `sitLimitOnDay`
                FROM `dailyreport`
                WHERE `detectDate` = %s AND `accountID` = %s
            """

            # Execute SQL query with parameters
            cursor.execute(sql_query, (date, acc_id))

            # Fetch a single row
            
            record = cursor.fetchone()
            records = cursor.fetchall()

            # Display data if a record is found
            if record:
                print("")
            else:
                record = None
            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")
    return record

def add_profileAxis(connection, acc_id):
    acc_id = int(acc_id)
    head = 999
    arm = 999
    back = 999
    leg = 999
    imgPath = None  # ควรตรวจสอบว่าฐานข้อมูลรับค่า None ได้หรือไม่

    if connection.is_connected():
        try:
            cursor = connection.cursor()

            # Perform the insert
            sql_query = """
                INSERT INTO `profilesaxis` (
                    `accountID`,
                    `headDegree`,
                    `armDegree`,
                    `backDegree`,
                    `legDegree`,
                    `profileImgPath`
                    )
                VALUES (%s, %s, %s, %s, %s, %s)
            """
            cursor.execute(sql_query, (acc_id, head, arm, back, leg, imgPath))

            # Commit the transaction
            connection.commit()

            # Check if the insert was successful
            return cursor.rowcount > 0

        except mysql.connector.Error as error:
            connection.rollback()  # Rollback if an error occurs
            print(f"Error: {error}")  # Logging the error for debugging
            return False

        finally:
            cursor.close()
    else:
        return False

def update_profileAxis(connection, acc_id, head, arm, back, leg, imgPath):
    acc_id = int(acc_id)

    if connection.is_connected():
        try:
            cursor = connection.cursor()

            # Perform the update
            sql_query = """
                UPDATE `profilesaxis` 
                SET 
                    `headDegree`=%s,
                    `armDegree`=%s,
                    `backDegree`=%s,
                    `legDegree`=%s,
                    `profileImgPath`=%s 
                WHERE `accountID`=%s
            """
            cursor.execute(sql_query, (head, arm, back, leg, imgPath, acc_id))

            # Commit the transaction
            connection.commit()
            print("update profileAxis is OK")
            # Check if any row was updated
            #if cursor.rowcount == 0:
            #    # No rows were affected, so nothing was updated
            #    return False
            #else:
            #    return True
            return True

        except mysql.connector.Error as error:
            # Rollback if any error occurs
            connection.rollback()
            print(f"Error: {error}")  # Logging the error for debugging
            return False

        finally:
            cursor.close()  # Ensure cursor is always closed
    else:
        print("Error: conn")
        return False

def update_calibratedDT(connection, acc_id):
    acc_id = int(acc_id)
    if connection.is_connected():
        try:
            cursor = connection.cursor()

            # Perform the update
            sql_query = """
                UPDATE `member` 
                SET 
                    `calibratedDT` = NOW()
                WHERE `accountID` = %s
            """
            cursor.execute(sql_query, (acc_id,))

            # Commit the transaction
            connection.commit()
            print("update Calibrated DT")
            return True
        except mysql.connector.Error as error:
            print(f"Error: {error}")
            return False

        finally:
            if cursor:
                cursor.close()
    else:
        return False

def update_lastLoginDT(connection, acc_id,):
    acc_id = int(acc_id)
    if connection.is_connected():
        try:
            cursor = connection.cursor()

            # Perform the update
            sql_query = """
                UPDATE `member` 
                SET 
                    `lastLoginDT` = NOW()
                WHERE `accountID` = %s
            """
            cursor.execute(sql_query, (acc_id,))

            # Commit the transaction
            connection.commit()
            print("update Login DT")
            return True
        except mysql.connector.Error as error:
            print(f"Error: {error}")
            return False

        finally:
            if cursor:
                cursor.close()
    else:
        return False

def update_lastDetectDT(connection, acc_id,):
    acc_id = int(acc_id)
    if connection.is_connected():
        try:
            cursor = connection.cursor()

            # Perform the update
            sql_query = """
                UPDATE `member` 
                SET 
                    `lastDetectDT` = NOW()
                WHERE `accountID` = %s
            """
            cursor.execute(sql_query, (acc_id,))

            # Commit the transaction
            connection.commit()
            print("update Detect DT")
            return True
        except mysql.connector.Error as error:
            print(f"Error: {error}")
            return False

        finally:
            if cursor:
                cursor.close()
    else:
        return False

#for mobile and web
def get_Login(connection,email):
    if connection.is_connected():
        try:
            cursor = connection.cursor(dictionary=True)
            sql_query = """
                SELECT `accountID`      AS accountID,
                    `name`              AS name,
                    `detectFreq`        AS detectFreq, 
                    `sitLimit`          AS sitLimit, 
                    `sitLimitAlarmFreq` AS sitLimitAlarmFreq,
                    `newNotification`   AS newNotification
                FROM `member` 
                WHERE `email` = %s;
            """

            # Execute SQL query
            cursor.execute(sql_query, (email,))

            # Fetch a single row
            record = cursor.fetchone()

            # Display data if a record is found
            if record:
                print()
            else:
                record = None
                print("No record found with Email =", email)

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")

    return record

def add_user(connection, email, name):
    if connection.is_connected():
        try:
            
            detectFreqDefault, sitLimitDefault, sitLimitFreqDefault = get_defaultSetting(connection)
            
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor()

            # Insert query with parameterized values
            insert_query = """
                INSERT INTO `member`
                (`email`, `name`, `calibratedDT`, `detectFreq`, `sitLimit`, `sitLimitAlarmFreq`, `lastLoginDT`, `lastDetectDT`, `newNotification`) 
                VALUES 
                (%s, %s, NULL, %s, %s, %s, NULL, NULL, True)
            """
            
            # Execute the insert query
            cursor.execute(insert_query, (email, name, detectFreqDefault, sitLimitDefault, sitLimitFreqDefault))
            
            # Commit the transaction
            connection.commit()

            # Close cursor
            cursor.close()

            # Fetch the inserted record
            try:
                # Create a new cursor object to execute SELECT queries
                cursor = connection.cursor(dictionary=True)
                sql_query = """
                    SELECT `accountID` AS accountID,
                           `name` AS name,
                           `detectFreq` AS detectFreq, 
                           `sitLimit` AS sitLimit, 
                           `sitLimitAlarmFreq` AS sitLimitAlarmFreq,
                           `newNotification`    AS newNotification
                    FROM `member` 
                    WHERE `email` = %s;
                """

                # Execute SQL query
                cursor.execute(sql_query, (email,))

                # Fetch a single row
                record = cursor.fetchone()

                # Display data if a record is found
                if record:
                    print("Record found:", record)
                else:
                    print("No record found with email =", email)

                # Close cursor
                cursor.close()

            except mysql.connector.Error as error:
                print("Error fetching data: {}".format(error))
            
            return record
        
        except mysql.connector.Error as error:
            return f"Error inserting data: {error}"
        
    else:
        return "Cannot insert detected to MySQL"

def get_defaultSetting(connection):
    # กำหนดค่าเริ่มต้นเป็น 5
    detectFreqDefault, sitLimitDefault, sitLimitFreqDefault = 5, 5, 5
    
    if connection.is_connected():
        try:
            cursor = connection.cursor(dictionary=True)
            sql_query = """
                SELECT item FROM items 
                WHERE itemID IN (
                    SELECT defaultItemID 
                    FROM groups 
                    WHERE groupID IN (1, 2, 3)
                );
            """

            # Execute SQL query
            cursor.execute(sql_query)

            # Fetch all rows
            records = cursor.fetchall()

            # Process the records
            if records:
                # Assuming you want to get the first three records if available
                detectFreqDefault = records[0]['item'] if len(records) > 0 else detectFreqDefault
                sitLimitDefault = records[1]['item'] if len(records) > 1 else sitLimitDefault
                sitLimitFreqDefault = records[2]['item'] if len(records) > 2 else sitLimitFreqDefault
                
                print(f"Detect Frequency Default: {detectFreqDefault}")
                print(f"Sit Limit Default: {sitLimitDefault}")
                print(f"Sit Limit Frequency Default: {sitLimitFreqDefault}")
            else:
                print("No records found, using default values")

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")

    return detectFreqDefault, sitLimitDefault, sitLimitFreqDefault

def get_profileAxis_img(connection,acc_id):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)

            # SQL query with parameterized inputs
            sql_query = """
                SELECT `profileImgPath`
                FROM `profilesaxis` 
                WHERE `accountID` = %s
            """

            # Execute SQL query with parameter
            cursor.execute(sql_query, (acc_id,))

            # Fetch a single row
            record = cursor.fetchone()

            # Display data if a record is found
            if record:
                profile_img_path = record["profileImgPath"]
            else:
                profile_img_path = None
                print(f"No record found with accountID = {acc_id}")

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print(f"Error fetching data: {error}")
            profile_img_path = None
    else:
        print("Cannot connect to MySQL")
        profile_img_path = None

    return profile_img_path

def get_dailyHistory(connection, acc_id, date):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)  # Use dictionary=True to get results as dictionaries

            # Example SQL query with parameterized queries
            sql_query = """
                SELECT `detectAmount`, 
                    `head`, `arm`, `back`, `leg`, 
                    `sitDuration`, 
                    `amountSitOverLimit`
                FROM `dailyreport`
                WHERE `detectDate` = %s AND `accountID` = %s
            """

            # Execute SQL query with parameters
            cursor.execute(sql_query, (date, acc_id))

            # Fetch a single row
            record = cursor.fetchone()

            # Display data if a record is found
            if record:
                print("")
            else:
                record = None
                print("No record found with accountID =", acc_id)

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")
    return record

def get_detectHistory(connection,acc_id,date):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)  # Use dictionary=True to get results as dictionaries

            # Example SQL query with parameterized queries
            sql_query = """
                SELECT `detectedID`, `detectDT`, `incorrectPoint`
                FROM `detected`
                WHERE DATE(`detectDT`) = %s AND `accountID` = %s
            """

            # Execute SQL query with parameters
            cursor.execute(sql_query, (date, acc_id))

            # Fetch a single row
            record = cursor.fetchall()

            # Display data if a record is found
            if record:
                print("")
            else:
                record = None
                print("No record found with accountID =", acc_id)

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")
    return record

def get_detectHistory_Img(connection,acc_id,date):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)  # Use dictionary=True to get results as dictionaries

            # Example SQL query with parameterized queries
            sql_query = """
                SELECT `detectedID`, `detectDT`, `incorrectPoint`, `evidence`
                FROM `detected`
                WHERE DATE(`detectDT`) = %s AND `accountID` = %s
            """

            # Execute SQL query with parameters
            cursor.execute(sql_query, (date, acc_id))

            # Fetch a single row
            record = cursor.fetchall()

            # Display data if a record is found
            if record:
                print("")
            else:
                record = None
                print("No record found with accountID =", acc_id)

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")
    return record

def get_detectImgPath(connection,acc_id,date):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)  # Use dictionary=True to get results as dictionaries

            # Example SQL query with parameterized queries
            sql_query = """
                SELECT `detectedID`, `evidence`
                FROM `detected`
                WHERE DATE(`detectDT`) = %s AND `accountID` = %s
            """

            # Execute SQL query with parameters
            cursor.execute(sql_query, (date, acc_id))

            # Fetch a single row
            record = cursor.fetchall()

            # Display data if a record is found
            if record:
                print("")
            else:
                record = None
                print("No record found with accountID =", acc_id)

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")
    return record

def add_detected(connection, acc_id, current_time, incorrectPoint, evidence):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor()

            # Insert query with parameterized values
            insert_query = """
                INSERT INTO `detected`(
                    `accountID`, 
                    `detectDT`, 
                    `incorrectPoint`, 
                    `evidence`
                ) VALUES (%s, %s, %s, %s)
            """
            
            # Execute the insert query
            cursor.execute(insert_query, (acc_id, current_time, incorrectPoint, evidence))
            
            # Commit the transaction
            connection.commit()

            # Close cursor
            cursor.close()
            update_lastDetectDT(connection, acc_id,)
            return "insert detected complete"
        except mysql.connector.Error as error:
            return f"Error inserting data: {error}"
    else:
        return "Cannot insert detected to MySQL"

def get_detect_max_min_DT_id(connection, acc_id):
    acc_id = int(acc_id)
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)  # Use dictionary=True to get results as dictionaries

            # Example SQL query with parameterized queries
            sql_query = """
                SELECT
                    MAX(detectDT) AS max_detectDT,
                    MIN(detectDT) AS min_detectDT
                FROM `detected`
                WHERE `accountID` = %s
            """

            # Execute SQL query with parameters (must be a tuple)
            cursor.execute(sql_query, (acc_id,))

            # Fetch a single row
            record = cursor.fetchone()

            # Display data if a record is found
            if record:
                print(f"Max DetectDT: {record['max_detectDT']}, Min DetectDT: {record['min_detectDT']}")
            else:
                record = None
                print(f"No record found with accountID = {acc_id}")

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print(f"Error fetching data: {error}")
    else:
        print("Cannot connect to MySQL")
    
    return record

def get_detect_max_min_DT_All(connection):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)  # Use dictionary=True to get results as dictionaries

            # Example SQL query with parameterized queries
            sql_query = """
                SELECT
                    MAX(detectDT) AS max_detectDT,
                    MIN(detectDT) AS min_detectDT
                FROM `detected`
            """

            # Execute SQL query with parameters (must be a tuple)
            cursor.execute(sql_query, ())

            # Fetch a single row
            record = cursor.fetchone()

            # Display data if a record is found
            if record:
                print(f"Max DetectDT: {record['max_detectDT']}, Min DetectDT: {record['min_detectDT']}")
            else:
                record = None
                print(f"No record found")

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print(f"Error fetching data: {error}")
    else:
        print("Cannot connect to MySQL")
    
    return record

def get_totalMonthDetect(connection,acc_id,year,month):
    if connection.is_connected():
        try:
            cursor = connection.cursor(dictionary=True)

            sql_query = """
                SELECT 
                    SUM(`head`)     AS total_head,
                    SUM(`arm`)      AS total_arm,
                    SUM(`back`)     AS total_back,
                    SUM(`leg`)      AS total_leg
                FROM `dailyreport`
                WHERE `accountID` = %s AND YEAR(`detectDate`) = %s AND MONTH(`detectDate`) = %s
            """
            cursor.execute(sql_query, (acc_id, year, month))
            record = cursor.fetchone()

            cursor.close()
            #return summary
            # Display data if a record is found
            if record:
                print("")
            else:
                record = None
                print("No record found with accountID =", acc_id)

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")
    return record

def get_monthHistory(connection,acc_id,year,month):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)  # Use dictionary=True to get results as dictionaries

            # Example SQL query with parameterized queries
            sql_query = """
                SELECT 
                    DAY(`detectDate`) AS day,
                    `sitDuration`,
                    `amountSitOverLimit`,
                    `sitLimitOnDay`
                FROM `dailyreport`
                WHERE `accountID` = %s AND YEAR(`detectDate`) = %s AND MONTH(`detectDate`) = %s
                ORDER BY `detectDate` ASC
            """

            cursor.execute(sql_query, (acc_id, year, month))
            record = cursor.fetchall()

            # Display data if a record is found
            if record:
                print("")
            else:
                record = None
                print("No record found with accountID =", acc_id)

            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")
    return record

def get_settingDefault(connection,acc_id):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)  # Use dictionary=True to get results as dictionaries

            # get default
            sql_query = """
                SELECT `groupID`, `groupName`, `defaultItemID`
                FROM `groups` 
                WHERE `groupID` BETWEEN 1 AND 3;
            """
            cursor.execute(sql_query)
            record = cursor.fetchall()

            # Display data if a record is found
            if record:
                print("")
            else:
                record = None
                print("No record found with accountID =", acc_id)

            # Close cursor
            cursor.close()
            
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")
    return record

def get_settingChoice(connection,acc_id):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)  # Use dictionary=True to get results as dictionaries

            # get default
            sql_query = """
                SELECT `groupID`, `item`
                FROM `items`
                WHERE `groupID` BETWEEN 1 AND 3
                ORDER BY CAST(`item` AS UNSIGNED);
            """
            cursor.execute(sql_query)
            record = cursor.fetchall()

            # Display data if a record is found
            if record:
                print("")
            else:
                record = None
                print("No record found with accountID =", acc_id)

            # Close cursor
            cursor.close()
            
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")
    return record

def update_setting(connection, acc_id, detectFreq, sitLimit, sitLimitFreq):
    if connection.is_connected():
        try:
            cursor = connection.cursor()

            # Perform the update
            sql_query = """
                UPDATE `member` 
                SET 
                    `detectFreq` = %s,
                    `sitLimit` = %s,
                    `sitLimitAlarmFreq` = %s  
                WHERE `accountID` = %s
            """
            cursor.execute(sql_query, (detectFreq, sitLimit, sitLimitFreq, acc_id))

            # Commit the transaction
            connection.commit()

            # Check if any row was updated
            if cursor.rowcount == 0:
                # Check if the current values are already the same
                cursor.execute("SELECT `detectFreq`, `sitLimit`, `sitLimitAlarmFreq` FROM `member` WHERE `accountID` = %s", (acc_id,))
                current_values = cursor.fetchone()
                
                if current_values and (current_values[0] == detectFreq and current_values[1] == sitLimit and current_values[2] == sitLimitFreq):
                    return True
                else:
                    return False
            else:
                return True

        except mysql.connector.Error as error:
            return False

        finally:
            cursor.close()
    else:
        return False

def get_noti(connection):
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor()  # Use dictionary=True to get results as dictionaries

            # get default
            sql_query = """
                SELECT DATE_FORMAT(startDate, '%Y-%m-%d') AS startDate, message
                FROM notification
                WHERE startDate <= CURDATE()
                AND (endDate >= CURDATE() OR endDate IS NULL);


            """
            cursor.execute(sql_query)
            record = cursor.fetchall()

            # Display data if a record is found
            if record:
                print("")
            else:
                record = None
                print("No record found")

            # Close cursor
            cursor.close()
            
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")
    return record

def set_userReadNoti(connection, acc_id):
    acc_id = int(acc_id)
    if connection.is_connected():
        cursor = None  # กำหนดค่าเริ่มต้นของ cursor
        try:
            cursor = connection.cursor()

            # คำสั่ง SQL สำหรับการอัปเดตข้อมูล
            sql_query = """
                UPDATE `member` 
                SET `newNotification` = False
                WHERE `accountID` = %s
            """
            cursor.execute(sql_query, (acc_id,))

            # ทำการ commit การเปลี่ยนแปลง
            connection.commit()
            print("Notification status updated successfully")
            return True
        except mysql.connector.Error as error:
            print(f"Error: {error}")
            return False
        finally:
            if cursor:
                cursor.close()
    else:
        print("Connection is not active")
        return False

def set_newNoti(connection):
    if connection.is_connected():
        cursor = None  # กำหนดค่าเริ่มต้นของ cursor
        try:
            cursor = connection.cursor()

            # คำสั่ง SQL สำหรับการอัปเดตข้อมูล (ลบ WHERE 1=1 เพราะไม่จำเป็น)
            sql_query = """
                UPDATE `member` 
                SET `newNotification` = True
            """
            cursor.execute(sql_query)

            # ทำการ commit การเปลี่ยนแปลง
            connection.commit()
            print("Notification status updated to True successfully")
            return True
        except mysql.connector.Error as error:
            print(f"SQL Error: {error}")
            return False
        finally:
            if cursor:
                cursor.close()
    else:
        print("Connection is not active")
        return False

def get_newNoit(connection,acc_id):
    acc_id = int(acc_id)
    if connection.is_connected():
        try:
            # Create a cursor object to execute SQL queries
            cursor = connection.cursor(dictionary=True)


            # Example SQL query with string formatting
            sql_query = """
                SELECT  `newNotification`
                FROM `member`
                WHERE `accountID` = %s
            """


            # Execute SQL query
            cursor.execute(sql_query, (acc_id,))


            # Fetch a single row
            record = cursor.fetchone()


            # Display data if a record is found
            if record:
                newNotification = record
            else:
                record = None
                print("No record found with accountID =", acc_id)


            # Close cursor
            cursor.close()
        except mysql.connector.Error as error:
            print("Error fetching data: {}".format(error))
    else:
        print("Cannot connect to MySQL")


    return bool(record["newNotification"])

def pre_delete_user(connection, accID, startDate, endDate):
    if connection.is_connected():
        accID = int(accID)
        cursor = None
        try:
            cursor = connection.cursor()

            # คำสั่ง SQL สำหรับการนับจำนวนข้อมูลตามช่วง datetime
            sql_query = """
                SELECT COUNT(*) 
                FROM `detected`
                WHERE `detectDT` BETWEEN %s AND %s
                AND `accountID` = %s
            """

            # Execute the query
            cursor.execute(sql_query, (startDate, endDate, accID))

            # Fetch the result (จำนวนข้อมูลที่เลือก)
            result = cursor.fetchone()
            record_count = result[0]

            print(f"Number of records found: {record_count}")
            return record_count
        except mysql.connector.Error as error:
            print(f"Error: {error}")
            return None
        finally:
            if cursor:
                cursor.close()
    else:
        print("Connection is not active")
        return None

def delete_detected_by_user(connection, accID, startDate, endDate):
    if connection.is_connected():
        accID = int(accID)
        cursor = None
        try:
            cursor = connection.cursor()

            # คำสั่ง SQL สำหรับการลบข้อมูลตามช่วง datetime
            sql_query = """
                DELETE FROM `detected`
                WHERE `detectDT` BETWEEN %s AND %s
                AND `accountID` = %s
            """

            # Execute the query
            cursor.execute(sql_query, (startDate, endDate, accID))

            # Commit the transaction
            connection.commit()

            print("Records deleted successfully")
            return True
        except mysql.connector.Error as error:
            print(f"Error: {error}")
            return False
        finally:
            if cursor:
                cursor.close()
    else:
        print("Connection is not active")
        return False

def delete_detected_all(connection, startDate, endDate):
    if connection.is_connected():
        cursor = None
        try:
            cursor = connection.cursor()

            # คำสั่ง SQL สำหรับการลบข้อมูลตามช่วง datetime
            sql_query = """
                DELETE FROM `detected`
                WHERE `detectDT` BETWEEN %s AND %s
            """

            # Execute the query
            cursor.execute(sql_query, (startDate, endDate))

            # Commit the transaction
            connection.commit()

            print("Records deleted successfully")
            return True
        except mysql.connector.Error as error:
            print(f"Error: {error}")
            return False
        finally:
            if cursor:
                cursor.close()
    else:
        print("Connection is not active")
        return False


# เรียกใช้งานฟังก์ชันเชื่อมต่อ
#connection = connect_to_mysql()

# เรียกใช้งานฟังก์ชันการดึงข้อมูล
#fetch_data(connection)
#print(str(get_noti(connection)))
#print(str(update_calibratedDT(connection, 3)))
#a = get_detect_max_min_DT_id(connection,3)
#print(str(delete_detected_all(connection, "2024-09-21", "2024-09-22")))
# ปิดการเชื่อมต่อ

#close_connection(connection)