class User:

    def __init__(self, username, account_number, wallet, number):
        self.username = username
        self.account_number = account_number
        self.accountId = wallet
        self.number = number
        self.verification_code = None

    def set_wallet(self, wallet):
        self.wallet = wallet

    def get_accountId(self):
        return self.accountId

    def get_account_number(self):
        return self.wallet

    def set_verification_code(self, verification_code):
        self.verification_code = verification_code