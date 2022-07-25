export class PreloaderScene extends Phaser.Scene {
  constructor() {
    super({
      key: 'PreloaderScene',
    })
  }

  preload(): void {
    var progressBar = this.add.graphics()
    var progressBox = this.add.graphics()
    progressBox.fillStyle(0x222222, 0.8)
    progressBox.fillRect(this.sys.canvas.width / 2 - 160, this.sys.canvas.height / 2 - 25, 320, 50)

    var percentText = this.make.text({
      x: this.sys.canvas.width / 2,
      y: this.sys.canvas.height / 2,
      text: '0%',
      style: {
        font: '18px monospace',
      },
    })
    percentText.setOrigin(0.5, 0.5)

    var assetText = this.make.text({
      x: this.sys.canvas.width / 2,
      y: this.sys.canvas.height / 2 + 50,
      text: '',
      style: {
        font: '18px monospace',
      },
    })
    assetText.setOrigin(0.5, 0.5)

    this.load.on('progress', (value: number) => {
      percentText.setText(`${Math.floor(value * 100)} %`)
      progressBar.clear()
      progressBar.fillStyle(0xffffff, 1)
      progressBar.fillRect(this.sys.canvas.width / 2 - 150, this.sys.canvas.height / 2 - 15, 300 * value, 30)
    })

    this.load.on('fileprogress', (file: any) => {
      assetText.setText('Loading asset: ' + file.key)
    })

    this.load.on('complete', () => {
      progressBar.destroy()
      progressBox.destroy()
      percentText.destroy()
      assetText.destroy()
      this.scene.start('ConnectWallet')
    })

    this.load.image('background', './assets/stars.png')
    this.load.image('titleLoading', './assets/title-loading.png')
    this.load.image('titleHome', './assets/title-home.png')
    this.load.image('buttonConnectWallet', './assets/button-connect-wallet.png')
    this.load.image('buttonConnectWalletHover', './assets/button-connect-wallet-hover.png')
    this.load.image('buttonLoading', './assets/button-loading.png')
    this.load.image('titleGameOver', './assets/title-game-over.png')
    this.load.image('bullet', './assets/enemy-blue-bullet.png')
    this.load.image('particleBlue', './assets/particles/blue.png')
    this.load.image('particleRed', './assets/particles/red.png')
    this.load.spritesheet('explosion', './assets/explosion.png', { frameWidth: 64, frameHeight: 64, endFrame: 23 })
    this.load.image('spark0', 'assets/particles/blue.png')
    this.load.image('spark1', 'assets/particles/red.png')
    this.load.image('buttonInventory', './assets/button-inventory.png')
    this.load.image('buttonBack', './assets/button-back.png')
    this.load.image('buttonRetry', './assets/button-retry.png')
    this.load.image('buttonShop', './assets/button-shop.png')
    this.load.image('buttonInventoryHover', './assets/button-inventory-hover.png')
    this.load.image('buttonBackHover', './assets/button-back-hover.png')
    this.load.image('buttonRetryHover', './assets/button-retry-hover.png')
    this.load.image('buttonShopHover', './assets/button-shop-hover.png')
    this.load.image('titleInventory', './assets/title-inventory.png')
    this.load.image('titleShop', './assets/title-shop.png')
    this.load.image('subtitleInventory', './assets/subtitle-inventory.png')
    this.load.image('subtitleMerchant', './assets/subtitle-merchant.png')
    this.load.image('cell', './assets/cell.png')
    this.load.image('cellHover', './assets/cell-hover.png')
    this.load.image('bigCell', './assets/big-cell.png')
    this.load.image('bigCellHover', './assets/big-cell-hover.png')
    this.load.image('titleSelectShip', './assets/title-select-ship.png')
    this.load.image('buttonMint', './assets/button-mint.png')
    this.load.image('buttonMintHover', './assets/button-mint-hover.png')
    this.load.image('bgFlare', './assets/bg-flare.png')
    this.load.image('bgFlare2', './assets/bg-flare2.png')
    this.load.image('bgHome', './assets/bg-home.png')

    this.load.image('partCabin0', './assets/parts/cabin0.png')
    this.load.image('partCabin1', './assets/parts/cabin1.png')
    this.load.image('partCabin2', './assets/parts/cabin2.png')
    this.load.image('partCabin3', './assets/parts/cabin3.png')
    this.load.image('partWeapon0', './assets/parts/weapon0.png')
    this.load.image('partWeapon1', './assets/parts/weapon1.png')
    this.load.image('partWeapon2', './assets/parts/weapon2.png')
    this.load.image('partWeapon3', './assets/parts/weapon3.png')
    this.load.image('partWing0', './assets/parts/wing0.png')
    this.load.image('partWing1', './assets/parts/wing1.png')
    this.load.image('partWing2', './assets/parts/wing2.png')
    this.load.image('partWing3', './assets/parts/wing3.png')
    this.load.image('partEngine0', './assets/parts/engine0.png')
    this.load.image('partEngine1', './assets/parts/engine1.png')
    this.load.image('partEngine2', './assets/parts/engine2.png')
    this.load.image('partEngine3', './assets/parts/engine3.png')

    this.load.image('itemCabin0', './assets/items/cabin0.png')
    this.load.image('itemCabin1', './assets/items/cabin1.png')
    this.load.image('itemCabin2', './assets/items/cabin2.png')
    this.load.image('itemCabin3', './assets/items/cabin3.png')
    this.load.image('itemWeapon0', './assets/items/weapon0.png')
    this.load.image('itemWeapon1', './assets/items/weapon1.png')
    this.load.image('itemWeapon2', './assets/items/weapon2.png')
    this.load.image('itemWeapon3', './assets/items/weapon3.png')
    this.load.image('itemWing0', './assets/items/wing0.png')
    this.load.image('itemWing1', './assets/items/wing1.png')
    this.load.image('itemWing2', './assets/items/wing2.png')
    this.load.image('itemWing3', './assets/items/wing3.png')
    this.load.image('itemEngine0', './assets/items/engine0.png')
    this.load.image('itemEngine1', './assets/items/engine1.png')
    this.load.image('itemEngine2', './assets/items/engine2.png')
    this.load.image('itemEngine3', './assets/items/engine3.png')

    this.load.image('inventoryitemCabin0', './assets/items/cabin0.png')
    this.load.image('inventoryitemCabin1', './assets/items/cabin1.png')
    this.load.image('inventoryitemCabin2', './assets/items/cabin2.png')
    this.load.image('inventoryitemCabin3', './assets/items/cabin3.png')
    this.load.image('inventoryitemWeapon0', './assets/items/weapon0.png')
    this.load.image('inventoryitemWeapon1', './assets/items/weapon1.png')
    this.load.image('inventoryitemWeapon2', './assets/items/weapon2.png')
    this.load.image('inventoryitemWeapon3', './assets/items/weapon3.png')
    this.load.image('inventoryitemWing0', './assets/items/wing0.png')
    this.load.image('inventoryitemWing1', './assets/items/wing1.png')
    this.load.image('inventoryitemWing2', './assets/items/wing2.png')
    this.load.image('inventoryitemWing3', './assets/items/wing3.png')
    this.load.image('inventoryitemEngine0', './assets/items/engine0.png')
    this.load.image('inventoryitemEngine1', './assets/items/engine1.png')
    this.load.image('inventoryitemEngine2', './assets/items/engine2.png')
    this.load.image('inventoryitemEngine3', './assets/items/engine3.png')

    this.load.image('shopitemCabin0', './assets/items/cabin0.png')
    this.load.image('shopitemCabin1', './assets/items/cabin1.png')
    this.load.image('shopitemCabin2', './assets/items/cabin2.png')
    this.load.image('shopitemCabin3', './assets/items/cabin3.png')
    this.load.image('shopitemWeapon0', './assets/items/weapon0.png')
    this.load.image('shopitemWeapon1', './assets/items/weapon1.png')
    this.load.image('shopitemWeapon2', './assets/items/weapon2.png')
    this.load.image('shopitemWeapon3', './assets/items/weapon3.png')
    this.load.image('shopitemWing0', './assets/items/wing0.png')
    this.load.image('shopitemWing1', './assets/items/wing1.png')
    this.load.image('shopitemWing2', './assets/items/wing2.png')
    this.load.image('shopitemWing3', './assets/items/wing3.png')
    this.load.image('shopitemEngine0', './assets/items/engine0.png')
    this.load.image('shopitemEngine1', './assets/items/engine1.png')
    this.load.image('shopitemEngine2', './assets/items/engine2.png')
    this.load.image('shopitemEngine3', './assets/items/engine3.png')

    this.load.audio('backgroundMusic', './assets/sounds/epic-space.mp3')
    this.load.audio('shootSound', './assets/sounds/shoot.mp3')
    this.load.audio('engineSound', './assets/sounds/engine.mp3')
    this.load.audio('hitSound', './assets/sounds/hit.mp3')
    this.load.audio('clickSound', './assets/sounds/click.mp3')
    this.load.audio('explodeSound', './assets/sounds/explode.mp3')

  }

  create(): void {}

  update(): void {}
}
