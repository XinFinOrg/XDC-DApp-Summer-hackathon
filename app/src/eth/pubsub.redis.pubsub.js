// const redis = im('redis');

import PubSub from "pubsub-js";
import { executeTxn, showToasts, queryEvents } from '../service/service';
import { EthereumContext } from "../eth/context";

export async function checkConnections() {
  console.log("Pubsub is", PubSub)
}

var topic = "MakePayment";

export async function publish(msg) {
  console.log("i am in publish", PubSub, topic, msg)
  PubSub.publish(topic, msg);
}

export async function subscribe() {
  console.log("i am in subscribe", PubSub, topic);
  PubSub.subscribe(topic, (tpic, msg) => {
    console.log("subscribe", tpic, msg)
  });
}
