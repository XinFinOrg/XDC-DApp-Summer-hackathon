import click
from flask import Flask, render_template, request, redirect, url_for, flash, jsonify
from twilio.twiml.messaging_response import MessagingResponse
from messages import Messages

from registered_users import RegisteredUsers
from user import User
from wallet_handler import WalletHandler

app = Flask(__name__)

registered_users = RegisteredUsers()

wallet_handler = WalletHandler()

message_template = Messages()

@app.route('/')
def index():
    return 'XDC ChatPay'

@app.route('/chat', methods=['POST'])
def chat():

    resp = MessagingResponse()

    print(request.form)
    incoming_msg = request.values.get('Body', '').lower()

    responded = False

    if incoming_msg == 'hello xdc':
        resp.message(message_template.home_menu())
        responded = True

    elif incoming_msg == '1':
        resp.message(message_template.register_menu())
        responded = True

    elif incoming_msg == '2':
        incoming_msg = request.values.get('WaId', '').lower()
        user = registered_users.get_user(incoming_msg)
        if user is None:
            resp.message(message_template.wallet_not_found())
            responded = True
        else:
            wallet = user.get_wallet()
            resp.message(message_template.view_wallet(wallet_address=wallet))
            responded = True

    elif incoming_msg == '3':
        incoming_msg = request.values.get('WaId', '').lower()
        user = registered_users.get_user(incoming_msg)
        if user is None:
            resp.message(message_template.wallet_not_found())
            responded = True
        else:
            wallet = user.get_wallet()
            balance = wallet_handler.get_balance(wallet)
            resp.message(message_template.wallet_balance(balance=balance))
            responded = True 
        

    elif incoming_msg == '4':
        resp.message(message_template.new_payment())
        responded = True

    elif incoming_msg == '5':
        resp.message(message_template.top_up_menu())
        responded = True

    elif incoming_msg == '6':
        incoming_msg = request.values.get('WaId', '').lower()
        user = registered_users.get_user(incoming_msg)
        if user is None:
            resp.message(message_template.wallet_not_found())
            responded = True
        else:
            wallet = user.get_wallet()
            transactions = wallet_handler.get_transactions(wallet)
            resp.message(message_template.transaction_history(transactions=transactions))
            responded = True
        

    elif incoming_msg == '7':
        resp.message(message_template.withdraw_menu())
        responded = True

    elif incoming_msg == '8':
        resp.message(message_template.exit_menu())
        responded = True

    elif len(incoming_msg.split(" ")) == 2 and incoming_msg.split()[0] == 'register':
        name = incoming_msg.split()[1]
        account_number, accountId = wallet_handler.create_wallet(name)
        key = request.values.get('WaId', '').lower()
        user = registered_users.register(User(name,account_number,accountId, key))
        resp.message(message_template.wallet_created(accountId))
        responded = True

    elif len(incoming_msg.split(" ")) == 2 and incoming_msg.split()[0] == 'topup':
        voucher = incoming_msg.split()[1]
        user_number = request.values.get('WaId', '').lower()
        user = registered_users.get_user(user_number)
        topup_response = wallet_handler.buy_xdc(user.get_accountId())
        resp.message(message_template.top_up_success())
        responded = True

    elif len(incoming_msg.split(" ")) == 3 and incoming_msg.split()[0] == 'pay':
        wallet_address = incoming_msg.split()[1]
        amount = incoming_msg.split()[2]
        user_number = request.values.get('WaId', '').lower()
        user = registered_users.get_user(user_number)
        payment_response = wallet_handler.send_xdc(wallet_address, amount)
        resp.message(message_template.payment_sent(amount, wallet_address))
        responded = True

    if not responded:
        resp.message("Sorry, I don't understand.")
    return str(resp)

if __name__ == '__main__':
    app.run(debug=True)
    # app.run(host='localhost', port=8080, debug=True)

