import random


class Messages:
    def __init__(self):
        pass

    def home_menu(self):
        return """
        1. Register a wallet
2. View wallet
3. View balance
4. Send XDC
5. Buy XDC
6. Transactions
7. Withdraw XDC with 1Voucher
7. Exit
"""

    def register_menu(self):
        return """
        Please enter your username in one word:
example: register JohnSmith123
    """

    def wallet_created(self, wallet_address):
        return """
        Thank you for registering your wallet.
Your wallet address is: {0}
""".format(wallet_address)

    def wallet_not_created(self):
        return """
        Sorry, your wallet could not be created.
Please try again.
"""

    def wallet_not_found(self):
        return """
        Sorry, your wallet could not be found.
Please try again.
"""

    def view_wallet(self, wallet_address):
        return """
        Your wallet address is: {0}
        """.format(wallet_address)

    def wallet_balance(self, balance):
        return """
        Your wallet balance is: {0}
        """.format(balance)

    def new_payment(self):
        return """
        Enter; pay + wallet/number + an amount.
example: pay 123456789 100
"""

    def payment_sent(self, amount, address):
        return """
        You have sent {0} XDC to {1}
        """.format(amount, address)

    def payment_not_sent(self):
        return """
        Sorry, your payment could not be sent.
Please try again.
"""

    def payment_received(self, amount, address):
        return """
        You have received {0} XDC from {1}
        """.format(amount, address)
    
    def transaction_history(self, transactions):
        return """
        Your transaction history is:
    {0}
        """.format(transactions)

    def top_up_menu(self):
        return """
        Please enter topup + your 1Voucher reference number: 
example: topup 123456789
"""

    def top_up_success(self):
        return """
        You have successfully toped up your wallet with 50 XDC.
        """

    def top_up_failure(self):
        return """
        Sorry, your top up could not be completed.
Please try again.
""" 

    def withdraw_menu(self):    
        return """
        Please withdraw + the amount you wish to withdraw: 
example: withdraw 100
""" 

    def withdraw_success(self, amount):
        return """
        You have successfully withdrawn {0} XDC.
Your 1Voucher reference is: {1}
""".format(amount, self.generate_reference())  

    def withdraw_failure(self):
        return """
        Sorry, your withdrawal could not be completed.
Please try again.
"""

    def exit_menu(self):
        return """
        Thank you for using XDC ChatPay.
        """

    def generate_reference(self):
        return str(random.randint(1000000, 9999999))