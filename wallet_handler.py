from random import randint, random
import time
import requests

class WalletHandler:

    def __init__(self, wallet_api_key=""):
        self.wallet_url = 'https://api-eu1.tatum.io'
        self.wallet_api_key = 'd46a119e-d436-4955-9712-3a5e5cf48b06_100'
        self.xpub = ""
        self.mneumonic = ""
        self.accountId = ""
        self.headers = {'x-api-key': self.wallet_api_key, 'Content-Type': "application/json"}
        self.main_account_setup()
        self.account_number = 0000000000

    def main_account_setup(self):

        #setup main account
        response_object = requests.get(self.wallet_url + '/v3/xdc/wallet?mnemonic=string', headers=self.headers)
        self.xpub = response_object.json()['xpub']
        self.mneumonic = response_object.json()['mnemonic']
        print(response_object.json())

        #setup virtual xdc currency
        body = {
            "name":"VC_XDC",
            "supply":"10000000000",
            "basePair":"XDC",
            "baseRate":1,
            "customer":{
                "accountingCurrency":"USD",
                "customerCountry":"SA",
                "externalId":"123654",
                "providerCountry":"SA"},
            "description":"XDC ChatPay",
            "accountCode":"Main_Account",
            "accountNumber":"1234567890",
            "accountingCurrency":"USD"}
        response_object = requests.post(self.wallet_url + '/v3/ledger/virtualCurrency', json=body, headers=self.headers)
        if response_object.status_code == 200:
            print("Virtual Currency Created")
        else:
            print("Virtual Currency Failed to Create")
            print(response_object.json())
            self.get_virtual_xdc()
        print(response_object.json())

    def get_virtual_xdc(self):
        response = requests.get(self.wallet_url + '/v3/ledger/virtualCurrency' + '/VC_XDC', headers=self.headers)
        print(response.json())
        self.accountId = response.json()['accountId']
        return response.json()

    def get_balance(self, address):
        response = requests.get(self.wallet_url + f'/v3/ledger/account/{address}/balance', headers=self.headers)
        print(response.json())
        return response.json()['availableBalance']

    def send_xdc(self, sender_address, receiver_address, amount):
        body = {
            "senderAccountId": sender_address,
            "recipientAccountId": receiver_address,
            "amount": amount,
            "anonymous": False,
            "compliant": False,
            "transactionCode": "1_01_EXTERNAL_CODE",
            "paymentId": randint(1, 1000000000),
            "recipientNote": "xdc chatpay",
            "baseRate": 1,
            "senderNote": "xdc chatpay",
            }
        response = requests.post(self.wallet_url + '/v3/ledger/transaction', json=body, headers=self.headers)
        try:
            print(response.json())
            return response.json()['reference']
        except KeyError as error:
            print(response.json())
            print(error)
            return None

    def buy_xdc(self, address):
        body = {
            "senderAccountId": self.accountId,
            "recipientAccountId": address,
            "amount": '50',
            "anonymous": False,
            "compliant": False,
            "transactionCode": "1_01_EXTERNAL_CODE",
            "paymentId": randint(1, 1000000000),
            "recipientNote": "xdc chatpay",
            "baseRate": 1,
            "senderNote": "xdc chatpay",
            }
        response = requests.post(self.wallet_url + '/v3/ledger/transaction', json=body, headers=self.headers)
        try:
            print(response.json())
            return response.json()['reference']
        except KeyError as error:
            print(response.json())
            print(error)
            return None

    def get_transactions(self, address):
        body = { 
            "id" : address
            }
        response = requests.post(self.wallet_url + '/v3/ledger/transaction/account?pageSize=50&offset=0&count=false', json=body, headers=self.headers)
        print(response.json())
        return self.parse_transactions(response)

    def create_wallet(self, name):
        self.account_number += 1
        body =  {"currency":"VC_XDC",
            "customer":
                {
                    "accountingCurrency":"USD",
                    "customerCountry":"SA",
                    "externalId": name,
                    "providerCountry":"US"
                },
            "compliant":False,
            "accountCode":"TRANSACTIONAL_ACCOUNT",
            "accountingCurrency":"USD",
            "accountNumber": str(self.account_number)}
        response = requests.post(self.wallet_url + '/v3/ledger/account', json=body, headers=self.headers)
        print(response.json())
        return response.json()['accountNumber'], response.json()['id']

    def parse_transactions(self, transaction_list):
        parsed_transactions = []
        for transaction in transaction_list:
            parsed_transaction = {}
            parsed_transaction['counterAccount'] = transaction['counterAccountId']
            parsed_transaction['amount'] = transaction['amount']
            transaction_date = self.convert_epoch_time(str(transaction['created']))
            parsed_transaction['date'] = transaction_date
            parsed_transaction['reference'] = transaction['reference']
            parsed_transactions.append(parsed_transaction)
        return parsed_transactions
    
    def convert_epoch_time(self, epoch_time):
        epoch_str_to_epoch_milli_sec = epoch_time[:-3]+"."+epoch_time[-3:]
        epoch = float(epoch_str_to_epoch_milli_sec)
        return time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(epoch))