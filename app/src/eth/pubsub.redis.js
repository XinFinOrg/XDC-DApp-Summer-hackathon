// const redis = im('redis');

import { createClient } from 'redis';

const CHANNELS = {
    PAYMENT: 'PAYMENT'
};

let redisurl = 'redis://127.0.0.1:6379';
var publisher = createClient(redisurl);
var subscriber = createClient(redisurl);

console.log("publisher",publisher,subscriber)

export async function subscribeToChannels() {
    Object.values(CHANNELS).forEach(channel => {
        subscriber.subscribe(channel);
        console.log("Subscribe to Channel")
    });
}

export async function handleMessage(channel, message) {
    console.log(`Message received. Channel: ${channel}. Message: ${message}.`);
    const parsedMessage = JSON.parse(message);
    switch (channel) {
        case CHANNELS.PAYMENT:
            console.log("parsedMessage", parsedMessage)
            break;
        default:
            return;
    }

}

export async function publish({ channel, message }) {
    console.log("i am in publish");
    publisher.publish(channel, message, () => {
        console.log("i am in publish2");
        subscriber.subscribe(channel);
    });
}

export async function broadcastTransaction(transaction) {
    await subscribeToChannels();
    await publish({
        channel: CHANNELS.PAYMENT,
        message: JSON.stringify(transaction)
    });
}

export async function checkTxn(){
    console.log("I am called");
    subscriber.on(
        'message',
        (channel, message) => {
            console.log(`Message received. Channel: ${channel}. Message: ${message}.`);
            const parsedMessage = JSON.parse(message);
            switch (channel) {
                case CHANNELS.PAYMENT:
                    console.log("parsedMessage", parsedMessage)
                    break;
                default:
                    return;
            }
        }
        
    );
}

