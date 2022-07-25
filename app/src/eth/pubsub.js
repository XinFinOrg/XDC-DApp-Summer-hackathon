import PubSub from 'pubsub-js'

var CHANNEL = "PaymentEvent"

export async function publishEvent(input) {
    console.log("Iam here publishevent")
    PubSub.publish(CHANNEL, input);
}

export async function subscribeEvent() {
    console.log("Iam here subscribeevent")
    var token = PubSub.subscribe(CHANNEL, function (msg, data) {
        console.log(msg)
        console.log(data)
    });
    return token;
}

export async function unSubscribeEvent(token) {
    console.log("Iam here subscribeevent")
    PubSub.unsubscribe(token);
}


