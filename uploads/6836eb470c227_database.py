import mysql.connector

mydb = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="achiri"  
)

mycursor = mydb.cursor()
mycursor.execute(
    "CREATE TABLE IF NOT EXISTS DATA (matriculation_number VARCHAR(255), name VARCHAR(255), email VARCHAR(255), phone_number VARCHAR(255), course VARCHAR(255), created_at DATETIME, PRIMARY KEY (matriculation_number))"
)

matriculation_number = input("Enter your matriculation number: ")
name = input("Enter your name: ")
email = input("Enter your email: ")
phone_number = input("Enter your phone number: ")
course = input("Enter your course: ")
#created_at = input("Enter the date of registration (YYYY-MM-DD): ")

sql = "INSERT INTO DATA (matriculation_number, name, email, phone_number, course, created_at) VALUES (%s, %s, %s, %s, %s, NOW())"
val = (matriculation_number, name, email, phone_number, course)
mycursor.execute(sql, val)
mydb.commit()
print("Data inserted successfully.")