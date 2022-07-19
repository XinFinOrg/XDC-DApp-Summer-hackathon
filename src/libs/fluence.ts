import { Fluence } from '@fluencelabs/fluence';
import { krasnodar } from '@fluencelabs/fluence-network-environment';
import { ref } from "vue"

import { connectState } from "./connect"

import { ping, reply, send, registerGreenChat } from '../_aqua/greenchat';

const relayNodes = [krasnodar[4], krasnodar[5], krasnodar[6]];

export const connectFluence = async () => {

	for(const i in relayNodes){
		const relayPeerId = relayNodes[i];

		try{
			await Fluence.start({ connectTo: relayPeerId.multiaddr });

			connectState.fluenceRelayId = relayPeerId.peerId;

			registerGreenChat({
				ping: (from:string) => {
					connectState.fluenceOnline[from] = true;
					//reply ping
					reply(from, connectState.fluenceRelayId);
					
					return from;
				},
				reply: (from:string) => {
					connectState.fluenceOnline[from] = true;
					return from;
				},
				send: (from:string, address:string, msg:string, timestamp:string) => {

					const info = {
						from: address.toLowerCase(),
						to: connectState.userAddr.value.toLowerCase(),
						msg: msg,
						timestamp: Number(timestamp),
						peer: true,
					};

					//push message to cache
					connectState.fluenceChatMessages.value.push(info);

					connectState.fluenceChatNewMessages.value.unshift(info);

					return [from, address, msg, timestamp];
				},
			});

			break;
		}catch(e){
			continue;
		}
	}

    return Fluence.getStatus().peerId!;
}

export const checkOnline = async (peerId:string) => {
	try{
		connectState.fluenceOnline[peerId] = false;
		await ping(peerId, connectState.fluenceRelayId);
		return true;
	}catch(e){
		return false;
	}
}

export const sendMessage = async (peerId:string, address:string, msg:string, timestamp:string) => {
	try{
		await send(peerId, connectState.fluenceRelayId, address, msg, timestamp);

		return true;
	}catch(e){
		return false;
	}
}