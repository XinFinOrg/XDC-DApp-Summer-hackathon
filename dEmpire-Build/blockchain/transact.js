let deploy_contract;

async function load(){
  console.log("loadcalled")
  var jsonFile = await fetch('blockchain/MainGame.json')
  jsonFile = await jsonFile.json();
  //console.log(jsonFile)

  var abi = jsonFile.abi;
  deploy_contract =  await new window.web3.eth.Contract(abi, "0xcA87B72497FB27843822A32bFea690B21fd1c1eB");

}

window.connect = async function(){
  if (typeof window.ethereum == 'undefined') {
  alert("Please install metamask")
}
//const accounts =await window.ethereum.request({ method: 'eth_requestAccounts' });
window.web3 = new Web3(ethereum);
const accounts =await window.web3.eth.getAccounts()

window.accounts = accounts[0].replace("0x","xdc");
console.log(window.accounts);
load();


myGameInstance.SendMessage('RTS_Camera','onConnect');

window.userdata()
}

window.openMarketPlace = () =>{
  window.open("https://market.dempire.space/")
}

window.startgame = async function(){
  let dd = await deploy_contract.methods.startgame().send({from:window.accounts})
  myGameInstance.SendMessage('RTS_Camera','onDone');
}

window.savegame = async function(str){
  console.log("we are in the js file of the metaverse")
  let tee = JSON.parse(str);
  let test = [0,0,0,0]
  for (let i =0;i<tee.buil.length;i++){
    if(tee.buil[i].buildingIndex<4){
    test[tee.buil[i].buildingIndex] =  test[tee.buil[i].buildingIndex]+1
  }
  }
  console.log("hi hi hi hi")
  console.log(test)
  let dd = await deploy_contract.methods.lockBase(test,window.data.data.user.minerid.id).send({from:window.accounts})
  myGameInstance.SendMessage('syncButton','onSave');
}

let graphkey = {
    0:"miner",
    1:"cannon",
    2:"xbow",
    3:"tesla",
    4:"archer",
    5:"robot",
    6:"valkyriee"
}
const keys = Object.keys(graphkey);
keys.forEach((item, i) => {
  window[graphkey[item]]=0;
});

window.collectwin = async function(buildingamount){
  let dd = await deploy_contract.methods.endwar(buildingamount).send({from:window.accounts});
}

window.userdata = async function(){

 let query = `query($id: String!) {
   user(id:$id){
       nfts{
         id
         owner {
           id
         }
         locked
         amount
         nft{
           id
           nftid
         }

       }
       aureus
       minerid {
         id
         locked
       }
       townhall
     }
}`
let variables = {
  id: window.accounts
}

  let data = await fetch('https://theta.overclockedbrains.co:8080/subgraphs/name/harshu4/rtsgame', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    query: query,
variables: variables

  }),
})
window.data = await data.json()
if(window.data.data.user == null || window.data.data.user.townhall == null ){
myGameInstance.SendMessage('RTS_Camera','onNewUser');
}
else{
    if(window.data.data.user.minerid != null){
      if(window.data.data.user.minerid.locked == false){
        window.miner = 1;
      }
    }
    window.aureus = window.data.data.user.aureus
  for(let i = 0;i<window.data.data.user.nfts.length;i++){
    console.log(graphkey[parseInt(window.data.data.user.nfts[i].nft.id)])
    window[graphkey[parseInt(window.data.data.user.nfts[i].nft.id)]] = parseInt(window.data.data.user.nfts[i].amount) - parseInt(window.data.data.user.nfts[i].locked)



}
    myGameInstance.SendMessage('RTS_Camera','onDone');
}

}
