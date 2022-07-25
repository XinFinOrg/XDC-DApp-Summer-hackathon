## XDC War Alpha

### Demo Video: https://youtu.be/UnkjOxt9ung

### Try it out now on https://xdc.waralpha.io

## About XDC War Alpha

XDC War Alpha is the first ever space shooter game with upgradable NFTs on XDC. Mint a basic spaceship to start with. Pilot it in the game and fight enemies! Harvest their parts. Upgrade your ship. Then sell your upgraded NFT.
![](https://xdc.waralpha.io/assets/screenshots/present-model.png)

We have created an NFT collection of 256 unique spaceships made of 4 cabins, 4 wings, 4 engines, and 4 weapons.
![](https://xdc.waralpha.io/assets/screenshots/present-parts.png)
![](https://xdc.waralpha.io/assets/screenshots/present-possibilities.png)

Go to https://xdc.waralpha.io to play the game. Click "connect your wallet". Metamask opens to authorize the connection. If you are not already connected to the XDC Apothem Testnet, Metamask will offer you to do so.
![](https://xdc.waralpha.io/assets/screenshots/connect-wallet-scene.png)

The game will then fetch all your spaceship NFTs from the smart contract. If you do not yet have a XDC War Alpha NFT, click "Mint New Ship" and Metamask will open to trigger the mint. You will receive a basic ship with entry-level weapons, wings, engine, and cabin. The ship will appear in your list of ships (if not refresh the page). Select that ship to access the game.
![](https://xdc.waralpha.io/assets/screenshots/select-ship-scene.png)

The game is built with PhaserJS, a 2D Javascript game engine that allows us to pilot our ship and fire at enemies. Use the directional arrows to move the ship and press the space bar to fire. Try to kill the enemy ship, but be careful not to get hit. When the enemy is destroyed, it drops some loot. Move your ship over it to get it into your inventory.
![](https://xdc.waralpha.io/assets/screenshots/gameplay1.png)

Then open your inventory to see all the parts you have found. Drag and drop a ship part to its corresponding area on your ship to upgrade that part. A XDC transaction opens that will actually modify your NFT metadata and image with the new part.
![](https://xdc.waralpha.io/assets/screenshots/inventory-scene.png)

The smart contract is a modified ERC721 with a new endpoint to modify an NFT metadata and image.

### How it's built

The GitHub repository is a mono-repo containing :

- The game, located in `src/game`, built with PhaserJS, a 2D javascript game engine

- The images and metadata generator for the NFTs, located in `src/generator`, a custom script that takes the 4 cabins, 4 wings, 4 engines, and 4 weapons and mixes them together to create the 256 combinations of JSON metadata and png files.

- The smart contracts for upgradable NFTs in `src/contracts`, which is a modified ERC721, created with OpenZepellin, Hardhat, and Typechain.

### What's next?

I want to create more parts to generate up to 10,000 unique ships and then sell the collection in order to finance the development of the game for more enemies, worlds, multiplayer, some storytelling, etc...
