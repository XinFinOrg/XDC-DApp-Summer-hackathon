(this.webpackJsonppolysign=this.webpackJsonppolysign||[]).push([[0],{239:function(f,e,t){},401:function(f,e,t){},402:function(f,e,t){"use strict";t.r(e);var n=t(0),r=t.n(n),a=t(47),c=t.n(a),s=(t(239),t(4)),i=t(9),b=t(5),o=t(27),d=t(408),u=t(409),l=t(59),j=t.p+"static/media/logo_3_2.98005b88.png",p=t(218),h=t.n(p),O="ckey_3cabf387ba184972a96d09006bb",x="699d821f-0de1-4e44-9fc4-46375611eea4",g="Polysign",m={80001:{name:"Mumbai",url:"https://mumbai.polygonscan.com/",id:80001},137:{name:"Matic Mainnet",url:"https://polygonscan.com/",id:137}},v=(Object.keys(m),m[80001]),y={title:"Renter agreement",description:"Please agree to the included renters agreement document",signerAddress:"0xD7e02fB8A60E78071D69ded9Eb1b89E372EE2292",files:[]};console.log("config",O,x,v);var w=t(410),k=t(7),C=["Free esignature request page hosting on IPFS","Completed esignatures saved on Smart Contracts","No vendor agreements required"];var S=function(f){var e=Object(o.f)();return Object(k.jsx)("div",{className:"hero-section",children:Object(k.jsxs)(d.a,{children:[Object(k.jsx)(u.a,{span:12,children:Object(k.jsxs)("div",{className:"hero-slogan-section",children:[Object(k.jsx)("div",{className:"hero-slogan",children:Object(k.jsxs)("p",{children:["Polygon-backed esignature requests"," for\xa0",Object(k.jsx)(h.a,{items:["businesses","individuals","everyone"]}),"."]})}),C.map((function(f,e){return Object(k.jsxs)("p",{children:[Object(k.jsx)(w.a,{twoToneColor:"#00aa00"}),"\xa0",f]})})),Object(k.jsx)("br",{}),Object(k.jsx)(l.a,{type:"primary",size:"large",onClick:function(){e("/create")},children:"Create esignature request"})]})}),Object(k.jsx)(u.a,{span:12,children:Object(k.jsx)("img",{src:j,className:"hero-image"})})]})})},N=t(8),M=t(2),T=t(1),I=t(406),D=t(227),A=t(222),q=t.n(A),E=function(f,e){var t="".concat("https://ipfs.io/ipfs","/").concat(f);return e?"".concat(t,"/").concat(e):t},_=function(f){return"".concat(window.location.origin,"/sign/").concat(f)};function z(f){return f.charAt(0).toUpperCase()+f.slice(1)}var R=function(f,e){return"".concat(v.url).concat(e?"tx/":"address/").concat(f)},U=function(f,e){return{title:z(f),dataIndex:f,key:f,render:e}};function F(f){if(0==f)return"0 Byte";var e=parseInt(Math.floor(Math.log(f)/Math.log(1024)));return Math.round(f/Math.pow(1024,e),2)+" "+["Bytes","KB","MB","GB","TB"][e]}var L=t(229),B={display:"flex",flexDirection:"row",flexWrap:"wrap",marginTop:16},J={display:"inline-flex",borderRadius:2,border:"2px dotted gray",marginBottom:8,marginRight:8,width:200,textAlign:"left",height:75,overflow:"hidden",padding:4,boxSizing:"border-box"},P={display:"flex",minWidth:0,overflow:"hidden"};function W(f){var e=f.files,t=f.setFiles,r=Object(L.a)({onDrop:function(f){console.log("files",f),t(f.map((function(f){return Object.assign(f,{preview:URL.createObjectURL(f)})})))}}),a=r.getRootProps,c=r.getInputProps,s=e.map((function(f){return Object(k.jsx)("div",{style:J,children:Object(k.jsx)("div",{style:P,children:Object(k.jsxs)("p",{children:[Object(k.jsx)("b",{children:f.name}),Object(k.jsx)("br",{}),f.size&&Object(k.jsxs)("span",{children:["Size: ",F(f.size),Object(k.jsx)("br",{})]}),f.type&&Object(k.jsxs)("span",{children:["Type: ",f.type]})]})})},f.name)}));return Object(n.useEffect)((function(){return function(){e.forEach((function(f){return URL.revokeObjectURL(f.preview)}))}}),[e]),Object(k.jsxs)("section",{children:[Object(k.jsxs)("div",Object(T.a)(Object(T.a)({},a({className:"dropzone"})),{},{children:[Object(k.jsx)("input",Object(T.a)({},c())),Object(k.jsx)("p",{children:"Drag 'n' drop some files here, or click to select files"})]})),Object(k.jsx)("br",{}),Object(k.jsx)("b",{children:"Files to upload:"}),Object(k.jsx)("aside",{style:B,children:s})]})}var V=t(89),Y=t.n(V),Z=t(220),Q="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJkaWQ6ZXRocjoweDAwM2VBZjdDMEIzNmNhOTdERDY2ODlhMEQzOUNlM0I4ODZEMzBEMTMiLCJpc3MiOiJ3ZWIzLXN0b3JhZ2UiLCJpYXQiOjE2Mjc3NTc0MTI3MDYsIm5hbWUiOiJoYWNrZnMtMjEifQ.53k0fUn-OY5ChOfID-VFINYBmg0ZSDsEzBpOzguArvI";function X(){return new Z.a({token:Q})}function G(f){return H.apply(this,arguments)}function H(){return(H=Object(i.a)(Object(s.a)().mark((function f(e){var t,n;return Object(s.a)().wrap((function(f){for(;;)switch(f.prev=f.next){case 0:return console.log("store",e),t=X(),f.next=4,t.put(e);case 4:return n=f.sent,console.log("stored files with cid:",n),f.abrupt("return",n);case 7:case"end":return f.stop()}}),f)})))).apply(this,arguments)}function K(f){var e="".concat(function(f,e){var t="https://ipfs.io/ipfs/".concat(f);return e?"".concat(t,"/").concat(e):t}(f),"/metadata.json");return Y.a.get(e)}var $=t(93),ff=[{inputs:[{internalType:"string",name:"_title",type:"string"},{internalType:"address",name:"_signerAddress",type:"address"}],stateMutability:"nonpayable",type:"constructor"},{anonymous:!1,inputs:[{indexed:!0,internalType:"address",name:"previousOwner",type:"address"},{indexed:!0,internalType:"address",name:"newOwner",type:"address"}],name:"OwnershipTransferred",type:"event"},{anonymous:!1,inputs:[{indexed:!1,internalType:"string",name:"oldStr",type:"string"},{indexed:!1,internalType:"string",name:"newStr",type:"string"}],name:"UpdatedResourceUrl",type:"event"},{inputs:[],name:"getResourceUrl",outputs:[{internalType:"string",name:"",type:"string"}],stateMutability:"view",type:"function"},{inputs:[],name:"getSigner",outputs:[{internalType:"address",name:"",type:"address"}],stateMutability:"view",type:"function"},{inputs:[],name:"getTitle",outputs:[{internalType:"string",name:"",type:"string"}],stateMutability:"view",type:"function"},{inputs:[{internalType:"string",name:"_nftUrl",type:"string"}],name:"markCompleted",outputs:[],stateMutability:"nonpayable",type:"function"},{inputs:[],name:"owner",outputs:[{internalType:"address",name:"",type:"address"}],stateMutability:"view",type:"function"},{inputs:[],name:"renounceOwnership",outputs:[],stateMutability:"nonpayable",type:"function"},{inputs:[{internalType:"address",name:"newOwner",type:"address"}],name:"transferOwnership",outputs:[],stateMutability:"nonpayable",type:"function"},{inputs:[{internalType:"string",name:"newUrl",type:"string"}],name:"updateResourceUrl",outputs:[],stateMutability:"nonpayable",type:"function"}],ef="0x60806040523480156200001157600080fd5b50604051620014e8380380620014e88339818101604052810190620000379190620003d8565b620000576200004b6200010460201b60201c565b6200010c60201b60201c565b62000087604051806060016040528060298152602001620014bf6029913983620001d060201b620005ef1760201c565b81600190805190602001906200009f9291906200029f565b5080600360006101000a81548173ffffffffffffffffffffffffffffffffffffffff021916908373ffffffffffffffffffffffffffffffffffffffff1602179055506000600560006101000a81548160ff021916908315150217905550505062000688565b600033905090565b60008060009054906101000a900473ffffffffffffffffffffffffffffffffffffffff169050816000806101000a81548173ffffffffffffffffffffffffffffffffffffffff021916908373ffffffffffffffffffffffffffffffffffffffff1602179055508173ffffffffffffffffffffffffffffffffffffffff168173ffffffffffffffffffffffffffffffffffffffff167f8be0079c531659141344cd1fd0a4f28419497f9722a3daafe3b4186f6b6457e060405160405180910390a35050565b620002728282604051602401620001e992919062000473565b6040516020818303038152906040527f4b5c4277000000000000000000000000000000000000000000000000000000007bffffffffffffffffffffffffffffffffffffffffffffffffffffffff19166020820180517bffffffffffffffffffffffffffffffffffffffffffffffffffffffff83818316178352505050506200027660201b60201c565b5050565b60008151905060006a636f6e736f6c652e6c6f679050602083016000808483855afa5050505050565b828054620002ad9062000593565b90600052602060002090601f016020900481019282620002d157600085556200031d565b82601f10620002ec57805160ff19168380011785556200031d565b828001600101855582156200031d579182015b828111156200031c578251825591602001919060010190620002ff565b5b5090506200032c919062000330565b5090565b5b808211156200034b57600081600090555060010162000331565b5090565b6000620003666200036084620004d7565b620004ae565b9050828152602081018484840111156200037f57600080fd5b6200038c8482856200055d565b509392505050565b600081519050620003a5816200066e565b92915050565b600082601f830112620003bd57600080fd5b8151620003cf8482602086016200034f565b91505092915050565b60008060408385031215620003ec57600080fd5b600083015167ffffffffffffffff8111156200040757600080fd5b6200041585828601620003ab565b9250506020620004288582860162000394565b9150509250929050565b60006200043f826200050d565b6200044b818562000518565b93506200045d8185602086016200055d565b62000468816200065d565b840191505092915050565b600060408201905081810360008301526200048f818562000432565b90508181036020830152620004a5818462000432565b90509392505050565b6000620004ba620004cd565b9050620004c88282620005c9565b919050565b6000604051905090565b600067ffffffffffffffff821115620004f557620004f46200062e565b5b62000500826200065d565b9050602081019050919050565b600081519050919050565b600082825260208201905092915050565b600062000536826200053d565b9050919050565b600073ffffffffffffffffffffffffffffffffffffffff82169050919050565b60005b838110156200057d57808201518184015260208101905062000560565b838111156200058d576000848401525b50505050565b60006002820490506001821680620005ac57607f821691505b60208210811415620005c357620005c2620005ff565b5b50919050565b620005d4826200065d565b810181811067ffffffffffffffff82111715620005f657620005f56200062e565b5b80604052505050565b7f4e487b7100000000000000000000000000000000000000000000000000000000600052602260045260246000fd5b7f4e487b7100000000000000000000000000000000000000000000000000000000600052604160045260246000fd5b6000601f19601f8301169050919050565b620006798162000529565b81146200068557600080fd5b50565b610e2780620006986000396000f3fe608060405234801561001057600080fd5b50600436106100885760003560e01c8063b3b2e5bb1161005b578063b3b2e5bb146100ef578063d2fd02be1461010d578063f2fde38b14610129578063ff3c1a8f1461014557610088565b8063715018a61461008d5780637ac3c02f146100975780638da5cb5b146100b557806391a8b36a146100d3575b600080fd5b610095610163565b005b61009f6101eb565b6040516100ac9190610a3b565b60405180910390f35b6100bd610215565b6040516100ca9190610a3b565b60405180910390f35b6100ed60048036038101906100e891906108c9565b61023e565b005b6100f761030e565b6040516101049190610a56565b60405180910390f35b610127600480360381019061012291906108c9565b6103a0565b005b610143600480360381019061013e91906108a0565b610465565b005b61014d61055d565b60405161015a9190610a56565b60405180910390f35b61016b61068b565b73ffffffffffffffffffffffffffffffffffffffff16610189610215565b73ffffffffffffffffffffffffffffffffffffffff16146101df576040517f08c379a00000000000000000000000000000000000000000000000000000000081526004016101d690610b26565b60405180910390fd5b6101e96000610693565b565b6000600360009054906101000a900473ffffffffffffffffffffffffffffffffffffffff16905090565b60008060009054906101000a900473ffffffffffffffffffffffffffffffffffffffff16905090565b61024661068b565b73ffffffffffffffffffffffffffffffffffffffff16610264610215565b73ffffffffffffffffffffffffffffffffffffffff16146102ba576040517f08c379a00000000000000000000000000000000000000000000000000000000081526004016102b190610b26565b60405180910390fd5b7f2da9e2df85c09953beceaa98fb363086a2bf1fbd193ef58aeeaa742a479c6be56002826040516102ec929190610aaf565b60405180910390a1806002908051906020019061030a929190610780565b5050565b60606002805461031d90610c41565b80601f016020809104026020016040519081016040528092919081815260200182805461034990610c41565b80156103965780601f1061036b57610100808354040283529160200191610396565b820191906000526020600020905b81548152906001019060200180831161037957829003601f168201915b5050505050905090565b600360009054906101000a900473ffffffffffffffffffffffffffffffffffffffff1673ffffffffffffffffffffffffffffffffffffffff163373ffffffffffffffffffffffffffffffffffffffff1614610430576040517f08c379a000000000000000000000000000000000000000000000000000000000815260040161042790610b06565b60405180910390fd5b8060049080519060200190610446929190610780565b506001600560006101000a81548160ff02191690831515021790555050565b61046d61068b565b73ffffffffffffffffffffffffffffffffffffffff1661048b610215565b73ffffffffffffffffffffffffffffffffffffffff16146104e1576040517f08c379a00000000000000000000000000000000000000000000000000000000081526004016104d890610b26565b60405180910390fd5b600073ffffffffffffffffffffffffffffffffffffffff168173ffffffffffffffffffffffffffffffffffffffff161415610551576040517f08c379a000000000000000000000000000000000000000000000000000000000815260040161054890610ae6565b60405180910390fd5b61055a81610693565b50565b60606001805461056c90610c41565b80601f016020809104026020016040519081016040528092919081815260200182805461059890610c41565b80156105e55780601f106105ba576101008083540402835291602001916105e5565b820191906000526020600020905b8154815290600101906020018083116105c857829003601f168201915b5050505050905090565b6106878282604051602401610605929190610a78565b6040516020818303038152906040527f4b5c4277000000000000000000000000000000000000000000000000000000007bffffffffffffffffffffffffffffffffffffffffffffffffffffffff19166020820180517bffffffffffffffffffffffffffffffffffffffffffffffffffffffff8381831617835250505050610757565b5050565b600033905090565b60008060009054906101000a900473ffffffffffffffffffffffffffffffffffffffff169050816000806101000a81548173ffffffffffffffffffffffffffffffffffffffff021916908373ffffffffffffffffffffffffffffffffffffffff1602179055508173ffffffffffffffffffffffffffffffffffffffff168173ffffffffffffffffffffffffffffffffffffffff167f8be0079c531659141344cd1fd0a4f28419497f9722a3daafe3b4186f6b6457e060405160405180910390a35050565b60008151905060006a636f6e736f6c652e6c6f679050602083016000808483855afa5050505050565b82805461078c90610c41565b90600052602060002090601f0160209004810192826107ae57600085556107f5565b82601f106107c757805160ff19168380011785556107f5565b828001600101855582156107f5579182015b828111156107f45782518255916020019190600101906107d9565b5b5090506108029190610806565b5090565b5b8082111561081f576000816000905550600101610807565b5090565b600061083661083184610b6b565b610b46565b90508281526020810184848401111561084e57600080fd5b610859848285610bff565b509392505050565b60008135905061087081610dda565b92915050565b600082601f83011261088757600080fd5b8135610897848260208601610823565b91505092915050565b6000602082840312156108b257600080fd5b60006108c084828501610861565b91505092915050565b6000602082840312156108db57600080fd5b600082013567ffffffffffffffff8111156108f557600080fd5b61090184828501610876565b91505092915050565b61091381610bcd565b82525050565b600061092482610bb1565b61092e8185610bbc565b935061093e818560208601610c0e565b61094781610d02565b840191505092915050565b6000815461095f81610c41565b6109698186610bbc565b945060018216600081146109845760018114610996576109c9565b60ff19831686526020860193506109c9565b61099f85610b9c565b60005b838110156109c1578154818901526001820191506020810190506109a2565b808801955050505b50505092915050565b60006109df602683610bbc565b91506109ea82610d13565b604082019050919050565b6000610a02603483610bbc565b9150610a0d82610d62565b604082019050919050565b6000610a25602083610bbc565b9150610a3082610db1565b602082019050919050565b6000602082019050610a50600083018461090a565b92915050565b60006020820190508181036000830152610a708184610919565b905092915050565b60006040820190508181036000830152610a928185610919565b90508181036020830152610aa68184610919565b90509392505050565b60006040820190508181036000830152610ac98185610952565b90508181036020830152610add8184610919565b90509392505050565b60006020820190508181036000830152610aff816109d2565b9050919050565b60006020820190508181036000830152610b1f816109f5565b9050919050565b60006020820190508181036000830152610b3f81610a18565b9050919050565b6000610b50610b61565b9050610b5c8282610c73565b919050565b6000604051905090565b600067ffffffffffffffff821115610b8657610b85610cd3565b5b610b8f82610d02565b9050602081019050919050565b60008190508160005260206000209050919050565b600081519050919050565b600082825260208201905092915050565b6000610bd882610bdf565b9050919050565b600073ffffffffffffffffffffffffffffffffffffffff82169050919050565b82818337600083830152505050565b60005b83811015610c2c578082015181840152602081019050610c11565b83811115610c3b576000848401525b50505050565b60006002820490506001821680610c5957607f821691505b60208210811415610c6d57610c6c610ca4565b5b50919050565b610c7c82610d02565b810181811067ffffffffffffffff82111715610c9b57610c9a610cd3565b5b80604052505050565b7f4e487b7100000000000000000000000000000000000000000000000000000000600052602260045260246000fd5b7f4e487b7100000000000000000000000000000000000000000000000000000000600052604160045260246000fd5b6000601f19601f8301169050919050565b7f4f776e61626c653a206e6577206f776e657220697320746865207a65726f206160008201527f6464726573730000000000000000000000000000000000000000000000000000602082015250565b7f4f6e6c79207468652064657369676e61746564207369676e65722063616e206360008201527f6f6d706c6574652074686520636f6e7472616374000000000000000000000000602082015250565b7f4f776e61626c653a2063616c6c6572206973206e6f7420746865206f776e6572600082015250565b610de381610bcd565b8114610dee57600080fd5b5056fea2646970667358221220f9b0f8cdcc13b51608cd03483a30b75cd4573c71f1add579d4e04d0e0ab3c01264736f6c634300080400334465706c6f79696e67206120506f6c797369676e20636f6e74726163742077697468207469746c653a",tf=function(){var f=Object(i.a)(Object(s.a)().mark((function f(){var e,t;return Object(s.a)().wrap((function(f){for(;;)switch(f.prev=f.next){case 0:return f.next=2,window.ethereum.enable();case 2:return t=new $.ethers.providers.Web3Provider(window.ethereum),e=t.getSigner(),f.abrupt("return",e);case 5:case"end":return f.stop()}}),f)})));return function(){return f.apply(this,arguments)}}();function nf(f,e){return rf.apply(this,arguments)}function rf(){return(rf=Object(i.a)(Object(s.a)().mark((function f(e,t){var n,r,a,c;return Object(s.a)().wrap((function(f){for(;;)switch(f.prev=f.next){case 0:return f.next=2,tf();case 2:return n=f.sent,r=new $.ethers.ContractFactory(ff,ef,n),a=$.ethers.utils.getAddress(t),f.next=7,r.deploy(e,a);case 7:return c=f.sent,f.next=10,c.deployed();case 10:return console.log("Contract deployed to address:",c.address),f.abrupt("return",c);case 12:case"end":return f.stop()}}),f)})))).apply(this,arguments)}var af=function(){var f=Object(i.a)(Object(s.a)().mark((function f(e,t){var n,r,a;return Object(s.a)().wrap((function(f){for(;;)switch(f.prev=f.next){case 0:if(e&&t){f.next=2;break}return f.abrupt("return",{});case 2:return f.next=4,tf();case 4:return n=f.sent,r=new $.ethers.Contract(e,ff,n),f.next=8,r.markCompleted(t);case 8:return a=f.sent,f.abrupt("return",a);case 10:case"end":return f.stop()}}),f)})));return function(e,t){return f.apply(this,arguments)}}(),cf=I.a.Step;var sf=function(f){var e=Object(n.useState)(Object(T.a)({},y)),t=Object(b.a)(e,2),r=t[0],a=t[1],c=Object(n.useState)(),o=Object(b.a)(c,2),j=o[0],p=o[1],h=Object(n.useState)(!1),O=Object(b.a)(h,2),x=O[0],g=O[1],m=Object(n.useState)(),v=Object(b.a)(m,2),w=v[0],C=v[1],S=function(f,e){a(Object(T.a)(Object(T.a)({},r),{},Object(M.a)({},f,e)))},A=function(f){return f.title&&f.description&&f.files.length>0&&function(f){try{return $.ethers.utils.getAddress(f),!0}catch(e){return!1}}(f.signerAddress)}(r),z=function(){var f=Object(i.a)(Object(s.a)().mark((function f(){var e,t,n,a,c,i,b,o;return Object(s.a)().wrap((function(f){for(;;)switch(f.prev=f.next){case 0:if(p(void 0),A){f.next=4;break}return p("Please provide a title, description, valid address, and at least one file."),f.abrupt("return");case 4:return g(!0),e=Object(T.a)({},r),t=e.files.map((function(f){return f})),n=Object(T.a)({},r),f.prev=8,f.next=11,nf(r.title,r.signerAddress);case 11:return a=f.sent,n.address=a.address,n.files=t.map((function(f){return f.path})),c=new Blob([JSON.stringify(n)],{type:"application/json"}),i=new File([c],"metadata.json"),b=[].concat(Object(N.a)(t),[i]),f.next=19,G(b);case 19:o=f.sent,n.cid=o,n.signatureUrl=_(o),n.contractUrl=R(n.address),C(n),f.next=31;break;case 27:f.prev=27,f.t0=f.catch(8),console.error("error creating esignature request",f.t0),p(f.t0.message||f.t0.toString());case 31:return f.prev=31,g(!1),f.finish(31);case 34:case"end":return f.stop()}}),f,null,[[8,27,31,34]])})));return function(){return f.apply(this,arguments)}}();return Object(k.jsx)("div",{children:Object(k.jsxs)(d.a,{children:[Object(k.jsx)(u.a,{span:16,children:Object(k.jsxs)("div",{className:"create-form white boxed",children:[Object(k.jsx)("h2",{children:"Create new esignature request"}),Object(k.jsx)("br",{}),Object(k.jsx)("h3",{className:"vertical-margin",children:"Esignature request title:"}),Object(k.jsx)(D.a,{placeholder:"Title of the esignature request",value:r.title,prefix:"Title:",onChange:function(f){return S("title",f.target.value)}}),Object(k.jsx)(q.a,{"aria-label":"Description",onChange:function(f){return S("description",f.target.value)},placeholder:"Description of the esignature request",prefix:"Description",value:r.description}),Object(k.jsx)("h3",{className:"vertical-margin",children:"Upload documents to esign:"}),Object(k.jsx)(W,{files:r.files,setFiles:function(f){return S("files",f)}}),Object(k.jsx)("h3",{className:"vertical-margin",children:"Enter signer address:"}),Object(k.jsx)("p",{children:"In order to sign or agree to the documents, the viewer or potential signer of the documents must prove ownership of a particular address"}),Object(k.jsx)(D.a,{placeholder:"Wallet address of signer",value:r.signerAddress,prefix:"Signer Address:",onChange:function(f){return S("signerAddress",f.target.value)}}),Object(k.jsx)("br",{}),Object(k.jsx)(l.a,{type:"primary",className:"standard-button",onClick:z,disabled:x,loading:x,children:"Create esignature request!"}),!j&&!w&&x&&Object(k.jsx)("span",{children:"\xa0Note this may take a few moments."}),Object(k.jsx)("br",{}),Object(k.jsx)("br",{}),j&&Object(k.jsx)("div",{className:"error-text",children:j}),w&&Object(k.jsxs)("div",{children:[Object(k.jsx)("div",{className:"success-text",children:"Created esignature request!"}),Object(k.jsx)("a",{href:E(w.cid),target:"_blank",children:"View metadata"}),Object(k.jsx)("br",{}),Object(k.jsx)("a",{href:w.contractUrl,target:"_blank",children:"View created contract"}),Object(k.jsx)("br",{}),Object(k.jsx)("br",{}),Object(k.jsxs)("p",{children:["Share this url with the potential signer:",Object(k.jsx)("br",{}),Object(k.jsx)("a",{href:w.signatureUrl,target:"_blank",children:"Open eSignature url"})]})]})]})}),Object(k.jsx)(u.a,{span:1}),Object(k.jsx)(u.a,{span:7,children:Object(k.jsx)("div",{className:"white boxed",children:Object(k.jsxs)(I.a,{className:"standard-margin",direction:"vertical",size:"small",current:w?2:A?1:0,children:[Object(k.jsx)(cf,{title:"Fill in fields",description:"Enter required data."}),Object(k.jsx)(cf,{title:"Create esignature request",description:"Requires authorizing a create esignature request operation."}),Object(k.jsx)(cf,{title:"Wait for esignature",description:"Your esignature request will be live for others to view and submit esignature."})]})})})]})})},bf=t(404),of=t(155),df=t(123),uf=t(405),lf=function(f,e){var t="https://api.covalenthq.com/v1/".concat(f,"/address/").concat(e,"/transactions_v2/?&key=").concat(O);return Y.a.get(t)},jf=df.a.Option,pf=[U("to_address"),U("value"),U("gas_spent"),U("block_signed_at",(function(f){return"".concat(new Date(f).toLocaleDateString()," ").concat(new Date(f).toLocaleTimeString())}))];var hf=function(f){var e=Object(n.useState)("0x73bceb1cd57c711feac4224d062b0f6ff338501e"),t=Object(b.a)(e,2),r=t[0],a=t[1],c=Object(n.useState)(v.id+""),o=Object(b.a)(c,2),d=o[0],u=o[1],j=Object(n.useState)(),p=Object(b.a)(j,2),h=p[0],O=p[1],x=Object(n.useState)(),y=Object(b.a)(x,2),w=y[0],C=y[1];Object(n.useEffect)((function(){C(void 0)}),[d]);var S=function(){var f=Object(i.a)(Object(s.a)().mark((function f(){var e;return Object(s.a)().wrap((function(f){for(;;)switch(f.prev=f.next){case 0:if(r&&d){f.next=3;break}return alert("Address and chainId are required"),f.abrupt("return");case 3:return O(!0),f.prev=4,f.next=7,lf(d,r);case 7:e=f.sent,C(e.data.data.items),f.next=15;break;case 11:f.prev=11,f.t0=f.catch(4),console.error(f.t0),alert("error getting signdata"+f.t0);case 15:return f.prev=15,O(!1),f.finish(15);case 18:case"end":return f.stop()}}),f,null,[[4,11,15,18]])})));return function(){return f.apply(this,arguments)}}();return Object(k.jsxs)("div",{children:[Object(k.jsxs)("p",{children:["This page can be used to lookup ",g," transactions against a given",v.name," address."]}),Object(k.jsx)(D.a,{value:r,onChange:function(f){return a(f.target.value)},prefix:"Address"}),Object(k.jsx)("br",{}),Object(k.jsx)("p",{}),Object(k.jsx)(df.a,{defaultValue:d,style:{width:120},onChange:function(f){return u(f)},children:Object.keys(m).map((function(f,e){return Object(k.jsx)(jf,{value:f,children:z(m[f].name)},e)}))}),"\xa0",Object(k.jsx)(l.a,{onClick:S,disabled:h,loading:h,children:"View transactions"}),Object(k.jsx)("br",{}),Object(k.jsx)("hr",{}),w&&Object(k.jsxs)("div",{children:[Object(k.jsx)("h1",{children:"Transaction History"}),Object(k.jsx)(uf.a,{dataSource:w,columns:pf,className:"pointer",onRow:function(f,e){return{onClick:function(e){console.log("event",e.target.value),window.open("".concat(m[d].url,"tx/").concat(f.tx_hash),"_blank")},onDoubleClick:function(f){},onContextMenu:function(f){},onMouseEnter:function(f){},onMouseLeave:function(f){}}}}),";"]})]})},Of=t(224),xf=t(226),gf=t.n(xf),mf=t(407);var vf=function(f){var e=Object(n.useState)(!1),t=Object(b.a)(e,2),r=t[0],a=t[1],c=Object(n.useState)(),s=Object(b.a)(c,2),i=s[0],o=s[1],d=Object(n.useRef)(),u=f.signId,j=f.contractAddress,p=f.authed,h=f.signerAddress,O=f.loading,x=f.sign,g=f.title,m=f.files;if(console.log("authorized",p),!p)return Object(k.jsxs)("div",{className:"centered",children:[Object(k.jsxs)("p",{children:["You are not logged in with the expected user for this esignature contract.",Object(k.jsx)("br",{}),Object(k.jsxs)("b",{className:"error-text",children:["Please log in with address: ",h]})]}),Object(k.jsx)("p",{children:Object(k.jsx)("a",{href:"/",children:"Return to home"})})]});var y=m||[];return console.log("files",y),Object(k.jsxs)("div",{children:[Object(k.jsxs)("div",{children:[Object(k.jsx)("h1",{children:f.title}),Object(k.jsx)("h3",{children:f.description})]}),Object(k.jsxs)("div",{children:[Object(k.jsxs)("a",{href:R(j),target:"_blank",children:["View Contract (",v.name,")"]}),Object(k.jsx)("br",{}),Object(k.jsx)("a",{href:E(u,"metadata.json"),target:"_blank",children:"View Request"})]}),Object(k.jsx)("br",{}),Object(k.jsx)("h3",{children:"Documents to acknowledge: "}),y.map((function(f,e){return Object(k.jsx)("li",{children:Object(k.jsx)("a",{href:"#",onClick:function(e){var t;e.preventDefault(),t=E(u,f),window.open(t,"_blank")},children:f})},e)})),Object(k.jsx)("br",{}),Object(k.jsx)("br",{}),Object(k.jsx)("p",{children:"By continuing, you agree to the documents listed and available for download above."}),Object(k.jsx)(l.a,{type:"primary",onClick:function(){return a(!0)},disabled:!p||0===y.length,children:"Accept Documents"}),Object(k.jsxs)(mf.a,{visible:r,title:"".concat(g,": Sign and complete"),footer:[Object(k.jsx)(l.a,{onClick:function(){return a(!1)},children:"Cancel"},"back"),Object(k.jsx)(l.a,{type:"primary",loading:O,onClick:function(){var f=d.current.toDataURL();x(f)},children:"Sign"},"submit")],children:[Object(k.jsx)(D.a,{placeholder:"Type name",value:i,prefix:"Type name:",onChange:function(f){return o(f.target.value)}}),Object(k.jsx)("br",{}),Object(k.jsx)("hr",{}),Object(k.jsx)("p",{children:"Draw signature:"}),Object(k.jsx)(gf.a,{ref:d,penColor:"green",canvasProps:{width:500,height:200,className:"sigCanvas"}})]})]})},yf=function(){var f=Object(i.a)(Object(s.a)().mark((function f(e,t,n,r){var a,c,i,b,o;return Object(s.a)().wrap((function(f){for(;;)switch(f.prev=f.next){case 0:return a={chain:"polygon",mint_to_address:n,description:t,name:e},c=new FormData,f.next=4,fetch(r);case 4:return f.next=6,f.sent.blob();case 6:return i=f.sent,b=new File([i],"signature.jpg",{type:"image/jpeg",lastModified:new Date}),c.append("file",b,b.name),o={method:"POST",url:"https://api.nftport.xyz/v0/files",params:a,headers:{"Content-Type":"multipart/form-data",Authorization:x,"content-type":"multipart/form-data; boundary=---011000010111000001101001"},data:c},f.abrupt("return",Y.a.request(o));case 11:case"end":return f.stop()}}),f)})));return function(e,t,n,r){return f.apply(this,arguments)}}(),wf=function(f){var e={method:"GET",url:"https://api.nftport.xyz/v0/mints/"+f,params:{chain:"polygon"},headers:{"Content-Type":"application/json",Authorization:x}};return Y.a.request(e)};var kf=function(f){var e=f.account,t=Object(o.g)().signId,r=Object(n.useState)({}),a=Object(b.a)(r,2),c=a[0],d=a[1],u=Object(n.useState)(!1),l=Object(b.a)(u,2),j=l[0],p=l[1],h=Object(n.useState)(),O=Object(b.a)(h,2),x=O[0],g=O[1],m=function(){var f=Object(i.a)(Object(s.a)().mark((function f(){var e;return Object(s.a)().wrap((function(f){for(;;)switch(f.prev=f.next){case 0:if(console.log("fetch",t),t){f.next=3;break}return f.abrupt("return");case 3:return p(!0),f.prev=4,f.next=7,K(t);case 7:e=f.sent,d(e.data),console.log("esignature request",e.data),f.next=16;break;case 12:f.prev=12,f.t0=f.catch(4),console.error(f.t0),alert("error getting signdata"+f.t0);case 16:return f.prev=16,p(!1),f.finish(16);case 19:case"end":return f.stop()}}),f,null,[[4,12,16,19]])})));return function(){return f.apply(this,arguments)}}();Object(n.useEffect)((function(){m()}),[t]);var v=Object(n.useMemo)((function(){return c&&(c.signerAddress||"").toLowerCase()===(e||"").toLowerCase()}),[c,e]),y=c.description,w=c.title,C=c.signerAddress,S=c.address,N=function(){var f=Object(i.a)(Object(s.a)().mark((function f(e){var n,r,a;return Object(s.a)().wrap((function(f){for(;;)switch(f.prev=f.next){case 0:return n={},p(!0),f.prev=2,f.next=5,yf(w,y,C,e);case 5:return r=f.sent,n.signatureNft=r.data,a=n.transaction_external_url,f.next=10,af(S,a||t);case 10:return r=f.sent,n=Object(T.a)({nftResults:n},r),f.prev=12,f.next=15,wf(r.hash);case 15:r=f.sent,n=Object(T.a)({nftResults:n},r),f.next=22;break;case 19:f.prev=19,f.t0=f.catch(12),console.error(f.t0);case 22:g(n),f.next=29;break;case 25:f.prev=25,f.t1=f.catch(2),console.error("error signing",f.t1),alert("Error completing esignature: "+JSON.stringify(f.t1));case 29:return f.prev=29,p(!1),f.finish(29);case 32:case"end":return f.stop()}}),f,null,[[2,25,29,32],[12,19]])})));return function(e){return f.apply(this,arguments)}}();return j?Object(k.jsx)("div",{className:"container",children:Object(k.jsx)(Of.a,{size:"large"})}):x?Object(k.jsxs)("div",{className:"container",children:[Object(k.jsx)("br",{}),Object(k.jsx)("br",{}),Object(k.jsx)("h1",{children:"Transaction complete!"}),Object(k.jsx)("p",{children:"Access your completed polygon contract and signature packet."}),Object(k.jsx)("a",{href:R(S),target:"_blank",children:"View Contract"}),Object(k.jsx)("p",{children:"Full response below:"}),Object(k.jsx)("pre",{children:JSON.stringify(x,null,"\t")})]}):Object(k.jsxs)("div",{className:"container boxed white",children:[Object(k.jsx)("h2",{className:"centered",children:"Sign Documents"}),Object(k.jsx)("br",{}),Object(k.jsx)(vf,Object(T.a)(Object(T.a)({},c),{},{authed:v,sign:N,signId:t}))]})},Cf=t.p+"static/media/logo.d4fbb84b.png",Sf=(t(401),bf.a.Header),Nf=bf.a.Content,Mf=bf.a.Footer;var Tf=function(){var f=Object(n.useState)(),e=Object(b.a)(f,2),t=e[0],r=e[1],a=Object(n.useState)(!1),c=Object(b.a)(a,2),d=c[0],u=c[1],j=function(){var f=Object(i.a)(Object(s.a)().mark((function f(){var e,t;return Object(s.a)().wrap((function(f){for(;;)switch(f.prev=f.next){case 0:if(u(!0),e=window.ethereum){f.next=5;break}return alert("Metamask must be connected to use Polysign"),f.abrupt("return");case 5:return f.prev=5,f.next=8,e.request({method:"eth_requestAccounts"});case 8:t=f.sent,console.log("accounts",t),r(t[0]),f.next=15;break;case 13:f.prev=13,f.t0=f.catch(5);case 15:return f.prev=15,u(!1),f.finish(15);case 18:case"end":return f.stop()}}),f,null,[[5,13,15,18]])})));return function(){return f.apply(this,arguments)}}(),p=function(){var f=Object(i.a)(Object(s.a)().mark((function f(){var e,t;return Object(s.a)().wrap((function(f){for(;;)switch(f.prev=f.next){case 0:if(e=window.ethereum){f.next=3;break}return f.abrupt("return");case 3:if(t=e.isConnected(),console.log("connected",t),!t){f.next=8;break}return f.next=8,j();case 8:case"end":return f.stop()}}),f)})));return function(){return f.apply(this,arguments)}}(),h=(Object(n.useEffect)((function(){p()}),[]),Object(o.f)()),O=window.location.pathname.startsWith("/sign");return Object(k.jsx)("div",{className:"App",children:Object(k.jsxs)(bf.a,{className:"layout",children:[Object(k.jsx)(Sf,{children:Object(k.jsxs)(of.a,{mode:"horizontal",defaultSelectedKeys:[],children:[Object(k.jsx)(of.a.Item,{children:Object(k.jsx)("img",{src:Cf,className:"header-logo pointer",onClick:function(){return h("/")}})},0),!O&&Object(k.jsxs)(k.Fragment,{children:[Object(k.jsx)(of.a.Item,{onClick:function(){return h("/create")},children:"Create esignature request"},1),Object(k.jsx)(of.a.Item,{onClick:function(){return h("/history")},children:"Lookup"},2)]}),!t&&Object(k.jsx)("span",{children:Object(k.jsx)(l.a,{type:"primary",onClick:j,loading:d,disabled:d,children:"Login with Metamask"})}),t&&Object(k.jsxs)("span",{children:["Hello: ",t]})]})}),Object(k.jsx)(Nf,{style:{padding:"0 50px"},children:Object(k.jsx)("div",{className:"container",children:Object(k.jsxs)(o.c,{children:[Object(k.jsx)(o.a,{path:"/",element:Object(k.jsx)(S,{})}),Object(k.jsx)(o.a,{path:"/sign/:signId",element:Object(k.jsx)(kf,{account:t})}),Object(k.jsx)(o.a,{path:"/create",element:Object(k.jsx)(sf,{account:t})}),Object(k.jsx)(o.a,{path:"/history",element:Object(k.jsx)(hf,{})})]})})}),Object(k.jsxs)(Mf,{style:{textAlign:"center"},children:[g," \xa92022 - A Polygon-powered esignature platform"]})]})})},If=t(115),Df=function(f){f&&f instanceof Function&&t.e(3).then(t.bind(null,411)).then((function(e){var t=e.getCLS,n=e.getFID,r=e.getFCP,a=e.getLCP,c=e.getTTFB;t(f),n(f),r(f),a(f),c(f)}))};c.a.render(Object(k.jsx)(r.a.StrictMode,{children:Object(k.jsx)(If.a,{children:Object(k.jsx)(Tf,{})})}),document.getElementById("root")),Df()}},[[402,1,2]]]);
//# sourceMappingURL=main.55e18d67.chunk.js.map