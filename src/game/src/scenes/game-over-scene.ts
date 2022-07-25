import { CONST } from '../const/const'

export class GameOver extends Phaser.Scene {
  constructor() {
    super({
      key: 'GameOver',
    })
  }

  init(): void {}

  preload(): void {}

  create(): void {
    this.add.tileSprite(
      this.sys.canvas.width / 2,
      this.sys.canvas.height / 2,
      this.sys.canvas.width,
      this.sys.canvas.height,
      'background',
    )

    this.add.tileSprite(
      this.sys.canvas.width / 2,
      this.sys.canvas.height / 2,
      this.sys.canvas.width,
      this.sys.canvas.height,
      'bgFlare2',
    )

    this.add.image(this.sys.canvas.width / 2, this.sys.canvas.height / 2, 'titleGameOver')

    let buttonRetry = this.add.image(550, this.sys.canvas.height - 100, 'buttonRetry')
    buttonRetry.setInteractive({ cursor: 'pointer' })
    buttonRetry.on('pointerover', () => buttonRetry.setTexture('buttonRetryHover'))
    buttonRetry.on('pointerout', () => buttonRetry.setTexture('buttonRetry'))
    buttonRetry.on('pointerdown', () => {
      this.sound.add('clickSound').play()
      this.scene.start('Game')
    })

    let buttonInventory = this.add.image(400, this.sys.canvas.height - 100, 'buttonInventory')
    buttonInventory.setInteractive({ cursor: 'pointer' })
    buttonInventory.on('pointerover', () => buttonInventory.setTexture('buttonInventoryHover'))
    buttonInventory.on('pointerout', () => buttonInventory.setTexture('buttonInventory'))
    buttonInventory.on('pointerdown', () => {
      this.sound.add('clickSound').play()
      this.scene.start('Inventory')
    })

    let buttonShop = this.add.image(250, this.sys.canvas.height - 100, 'buttonShop')
    buttonShop.setInteractive({ cursor: 'pointer' })
    buttonShop.on('pointerover', () => buttonShop.setTexture('buttonShopHover'))
    buttonShop.on('pointerout', () => buttonShop.setTexture('buttonShop'))
    buttonShop.on('pointerdown', () => {
      this.sound.add('clickSound').play()
      this.scene.start('Shop')
    })

    let buttonBack = this.add.image(100, this.sys.canvas.height - 100, 'buttonBack')
    buttonBack.setInteractive({ cursor: 'pointer' })
    buttonBack.on('pointerover', () => buttonBack.setTexture('buttonBackHover'))
    buttonBack.on('pointerout', () => buttonBack.setTexture('buttonBack'))
    buttonBack.on('pointerdown', () => {
      this.sound.add('clickSound').play()
      this.scene.start('SelectShip')
    })
  }

  update(): void {}
}
