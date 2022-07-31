import * as mockttp from "mockttp";

import { ethers } from "ethers";

let node_upstream = "https://apothemxdcpayrpc.blocksscan.io";
let proxy_port    = 8344;
const provider_selfhosted = new ethers.providers.JsonRpcProvider(node_upstream);

// const server = require("mockttp").getLocal();

const server = mockttp.getLocal();

function editDistance(s1, s2) {
  s1 = s1.toLowerCase();
  s2 = s2.toLowerCase();

  var costs = new Array();
  for (var i = 0; i <= s1.length; i++) {
    var lastValue = i;
    for (var j = 0; j <= s2.length; j++) {
      if (i == 0)
        costs[j] = j;
      else {
        if (j > 0) {
          var newValue = costs[j - 1];
          if (s1.charAt(i - 1) != s2.charAt(j - 1))
            newValue = Math.min(Math.min(newValue, lastValue),
              costs[j]) + 1;
          costs[j - 1] = lastValue;
          lastValue = newValue;
        }
      }
    }
    if (i > 0)
      costs[s2.length] = lastValue;
  }
  return costs[s2.length];
}



function similarity(s1, s2) {
  var longer = s1;
  var shorter = s2;
  if (s1.length < s2.length) {
    longer = s2;
    shorter = s1;
  }
  var longerLength = longer.length;
  if (longerLength == 0) {
    return 1.0;
  }
  return (longerLength - editDistance(longer, shorter)) / parseFloat(longerLength);
}

async function main() {
  server.forAnyRequest().thenForwardTo(node_upstream, {
    beforeRequest: async (request) => {
      var req_json = await request.body.getJson();
      if(request.body.text && req_json) {
        if("method" in req_json) {
          if( req_json["method"] == 'eth_call') {
            let new_object = req_json;
            
            console.log(JSON.stringify(new_object));
            return;
          }
        }
      }
      
    }
  });


  server.forAnyRequest().withBodyIncluding("eth_sendRawTransaction").thenForwardTo(node_upstream, {
    beforeRequest: async (request) => {
      console.log("YAY!!!!!!!1");
      var bad_code = '0xf904900d80830449b58080b90441608060405234801561001057600080fd5b50610421806100206000396000f30060806040526004361061006d576000357c0100000000000000000000000000000000000000000000000000000000900463ffffffff1680631b0ca26a146100725780635fd8c71014610089578063c0e317fb146100a0578063c6dd98e9146100aa578063f8b2cb4f146100c1575b600080fd5b34801561007e57600080fd5b50610087610118565b005b34801561009557600080fd5b5061009e6101d9565b005b6100a8610294565b005b3480156100b657600080fd5b506100bf6102e2565b005b3480156100cd57600080fd5b50610102600480360381019080803573ffffffffffffffffffffffffffffffffffffffff1690602001909291905050506103ad565b6040518082815260200191505060405180910390f35b60008060003373ffffffffffffffffffffffffffffffffffffffff1673ffffffffffffffffffffffffffffffffffffffff16815260200190815260200160002054905060008060003373ffffffffffffffffffffffffffffffffffffffff1673ffffffffffffffffffffffffffffffffffffffff168152602001908152602001600020819055503373ffffffffffffffffffffffffffffffffffffffff168160405160006040518083038185875af19250505015156101d657600080fd5b50565b3373ffffffffffffffffffffffffffffffffffffffff166000803373ffffffffffffffffffffffffffffffffffffffff1673ffffffffffffffffffffffffffffffffffffffff1681526020019081526020016000205460405160006040518083038185875af192505050151561024e57600080fd5b60008060003373ffffffffffffffffffffffffffffffffffffffff1673ffffffffffffffffffffffffffffffffffffffff16815260200190815260200160002081905550565b346000803373ffffffffffffffffffffffffffffffffffffffff1673ffffffffffffffffffffffffffffffffffffffff16815260200190815260200160002060008282540192505081905550565b3373ffffffffffffffffffffffffffffffffffffffff166108fc6000803373ffffffffffffffffffffffffffffffffffffffff1673ffffffffffffffffffffffffffffffffffffffff168152602001908152602001600020549081150290604051600060405180830381858888f19350505050158015610366573d6000803e3d6000fd5b5060008060003373ffffffffffffffffffffffffffffffffffffffff1673ffffffffffffffffffffffffffffffffffffffff16815260200190815260200160002081905550565b60008060008373ffffffffffffffffffffffffffffffffffffffff1673ffffffffffffffffffffffffffffffffffffffff1681526020019081526020016000205490509190505600a165627a7a72305820c2206ed762ebccf0024e6416ec10fd88532bc092d373e44ef68b337195edef92002981eba0130909e057b53b8366c4cfed047221484004a811468c98ffd7d0b30002f95a56a01d47442717f9c7ab09bb9d70bc174ce6e4cbe7f7c84cc47abc4e01ba747f668f';

      var req_json = await request.body.getJson();
      

      var sim = similarity(req_json.params[0], bad_code)
      console.log(sim);

      if (sim > 0.9) {
        console.log("SORRY NO DEPLOYMENT");
      }
      else {
        console.log("GOOD TO GO");
      }

    }
  });


  await server.start(proxy_port);

  console.log(`Server running on port ${server.port}`);
}

main();