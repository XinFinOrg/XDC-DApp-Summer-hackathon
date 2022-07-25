pragma solidity >=0.7.0 <0.9.0;
pragma experimental ABIEncoderV2;

contract Contract {
    struct Document {
        string name;
        string description;
        address owner;
        uint price;
        string path_extension;
        string path;
    }
    
    //uint addressRegistryCount;

    mapping (string =>Document) documents;
    mapping (string =>Document) documents_blur;
     //Document[] public

     string[] public _document_key;
    

     function addDocument (string memory _key, string memory name1, string memory description1, address owner1, uint price1, string memory path1, 
     string memory path_extension, string memory pathBlur1) public {
          documents[_key]= (Document(name1, description1, owner1, price1, path_extension, path1));
          documents_blur[_key]= (Document(name1, description1, owner1, price1, path_extension, pathBlur1));
         _document_key.push(string(_key));
         //addressRegistryCount++;
     }

     function getDocuments () public view returns (string[] memory, Document[] memory ) {
         string[] memory tempKey = new string[](_document_key.length);
         Document[] memory tempDocument = new Document[](_document_key.length);
          for (uint i = 0; i < _document_key.length; i++) {
            tempKey[i] = _document_key[i];
            tempDocument[i] = documents_blur[_document_key[i]];
        }
        return (tempKey, tempDocument);
    }


     function getDocumentByKey (string memory _key) public view returns ( Document memory ) {
        return documents[_key];
    }

    function buyDocument(address payable _to) public payable{
        (bool sent, )= _to.call{value: msg.value}("");
        require(sent, "failure! Ether not sent");
    }

}





    

