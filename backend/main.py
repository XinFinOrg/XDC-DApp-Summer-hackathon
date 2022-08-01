from fastapi import FastAPI, File, UploadFile, BackgroundTasks
import uvicorn
import datetime
import hashlib
from pydantic import BaseModel
from pymongo import MongoClient
from zipfile import ZipFile
import os
import time
import shutil
import random
import json
import slither_code
from fastapi.middleware.cors import CORSMiddleware
from dotenv import load_dotenv, find_dotenv

# import soli
# Configuration variables
load_dotenv(find_dotenv())
app = FastAPI()
result_list = []
destination = os.getenv("DESTINATION")
origins = os.getenv("ORIGINS")
result_destination = os.getenv("RESULT")
mongo_url = os.getenv("DB_URL")
host = os.getenv("HOST")
port = int(os.getenv("PORT"))

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# database connection
client = MongoClient(mongo_url)
db = client.smartcontract
collection = db["results"]


# md5sum
def md5checksum(fname):
    md5 = hashlib.md5()
    f = open(fname, "rb")
    while chunk := f.read(4096):
        md5.update(chunk)
    return md5.hexdigest()


# hash
def db_hash_result(hash):
    return db.collection.find_one({"hash": hash})


# store in MongoDB as a raw result
def store_db(hash, result_list):
    print(type(result_list))
    save_result = {}
    save_result['hash'] = hash
    save_result['result'] = result_list
    db.collection.insert_one(dict(save_result))
    return True


# json file parsing and getting result
def json_result_parsing(result_destination, json_names):
    with open(result_destination + json_names + ".json", "r") as file:
        json_file = json.load(file)
        empty_list = []
        result_dictionary = {}
        val = json_file["success"]
        err = json_file["error"]
        size = len(json_file["results"]["detectors"])
    for i in range(0, size):
        description = json_file["results"]["detectors"][i]["description"]
        impact = json_file["results"]["detectors"][i]["impact"]
        result_dictionary["DESCRIPTION"] = description
        result_dictionary["IMPACT"] = impact
        dictionary = result_dictionary.copy()
        empty_list.append(dictionary)
    results = {
        "success": val,
        "isCache": False,
        "error": err,
        "results": empty_list
    }
    print("success")
    result_list.append(results)
    return result_list


# json file parsing for db raw result to json result
def db_json_result_parsing(hash_result):
    hash_result = hash_result["result"]
    dict_result = hash_result[0]
    dict_result["isCache"] = True
    print("from database , we don't to scan again")
    return hash_result


# upload
@app.post('/upload')
async def file_upload(file: UploadFile = File(...)):
    try:
        content = await file.read()
        extension = file.filename.split('.')[1]
        if extension not in ['zip']:
            return {"message": "Only support Zip Files "}
        timestamp = datetime.datetime.now().strftime("%Y-%m-%d_%I-%M-%S_%p")
        file_name = destination + "/" + "file_" + timestamp + ".zip"
        print(file_name)
        with open(file_name, 'wb') as f:
            f.write(content)
        # check sum of zip file in db
        hash = md5checksum(file_name)
        hash_result = db_hash_result(hash)
        if (hash_result):
            hash_result = db_json_result_parsing(hash_result)
            file.close()
            # os.remove(file_name)
            return hash_result
        # Zip file Extract start
        with ZipFile(file_name, 'r') as zip:
            dirname = file_name.split('.')[0]
            zip.extractall(dirname)
        print("extracted..!")
        print("dirname", dirname)
        # zip file removed
        os.remove(file_name)
        print("zip file removed")
        print("scan starting ")
        result_error_dict = slither_code.compiler_version(dirname)
        if (type(result_error_dict) == dict):
            print("scan stop because unsupported version & files are removed..!")
            # os.remove(dirname)
            return result_error_dict
        print("scan completed and files are removed")
        json_names = dirname.split("/")[6]
        print("json_name:", json_names)
        # json file parsing
        result_list = json_result_parsing(result_destination, json_names)
        # store in Mongo
        if (store_db(hash, result_list)):
            print("result are stored..!")
        print("file uploaded:", file_name)
        return result_list
    except Exception as e:
        print("###############",e)
        print(e)
        return {"message": "Error Occurred. This could possibly be due to the usage of unsupported solidity versions. Please ensure your code has only solidity version 0.8.0  "}
    finally:
        file.close()


if __name__ == '__main__':
    uvicorn.run(app, host=host, port=port)

