## Inspiration
Although crytpto and blockchain has been on an increase in developing nations, there has been slow uptake due to low literacy levels, trust, device constraints and the lack of cash on/off ramps.

We are looking at building and launching XDC ChatPay as a simple yet effective remittance solution that addresses the challenges above. Being simple yet effective, our solution will drive users to use the remittance platform which has been built on the XDC network in order to increase adoption.

## What it does

XDC ChatPay allows users to easily on/off ramp by voucher top-ups or withdrawals directly to and from accounts using the [1Voucher](https://1foryou.com/) service, users are also able to send and receive funds in seconds. We are looking into adding additional functionalities like purchasing of utility vouchers, airtime, and allowing for scanning of QR codes for easy payments 

## How we built it

The MVP was built using:

**Twilio for the Whatsapp messaging functionality**
**Python Flask app for the backend**
**Tatum.io for the XDC chain account integration**
**Heroku for hosting**

## Challenges we ran into

When ran into challenges when conneting the XinFin api endpoints on Tatum for account creation, however I was able to overcome this and get it to work. Also, doing the user research proved to be challenging because of time constraints. The business approval time on the 1Voucher platform is lengthy so I was not able to include the live voucher authentication to the demo

## Accomplishments that we're proud of

We managed to build a working MVP using the mainnet endpoints, 

## What we learned

We learned a lot about the XinFin chain and also about designing simple yet effective systems.

## What's next for XDC ChatPay

Implement live voucher integration
Improve on application and user security
Add additional functionality to application

## TO run the application locally

- Create an account on Twilio
- Install ngrok
- Create a Python virtual environment
- PIP install the requirements.txt file
- Run the flask application
- Connect the ngrok endpoint to your Twilio account and start sending XDC ChatPay messages. 