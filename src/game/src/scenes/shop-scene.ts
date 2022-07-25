import { burnAlphas, getAlphaBalance, mintAlphas } from '../blockchain/lib'
import { CONST } from '../const/const'

export class Shop extends Phaser.Scene {
  private textBalance: Phaser.GameObjects.Text
  private counterInventory = 0
  private counterShop = 0
  private balanceTimer: Phaser.Time.TimerEvent

  constructor() {
    super({
      key: 'Shop',
    })
  }

  replaceAt = function (original: string, index: number, replacement: string) {
    return original.substring(0, index) + replacement + original.substring(index + replacement.length)
  }

  init(): void {
    getAlphaBalance()
  }

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

    this.add.image(this.sys.canvas.width / 2, 80, 'titleShop')
    this.add.image(500, 150, 'subtitleInventory')
    this.add.image(1100, 150, 'subtitleMerchant')

    this.textBalance = this.add.text(40, 20, `MY BALANCE: ${CONST.ALPHA_BALANCE} ALPHAS`, {
      fontFamily: 'Ethnocentric',
    })
    this.textBalance.updateText()

    this.balanceTimer = this.time.addEvent({
      delay: 5000,
      callback: () => {
        console.log('getAlphaBalance')
        getAlphaBalance()
      },
      callbackScope: this,
      loop: true,
    })

    // Inventory
    for (let j = 0; j < 4; j++) {
      for (let i = 0; i < 4; i++) {
        const cell = this.add.image(300 + i * 130, 250 + j * 130, 'cell')
        const itemName = CONST.INVENTORY[i + 4 * j]

        if (itemName) {
          const item = this.add.image(300 + i * 130, 250 + j * 130, 'inventory' + itemName)
          item.setInteractive({ cursor: 'move' })
          item.setDepth(2)

          item.on('pointerover', () => {
            cell.setTexture('cellHover')
          })
          item.on('pointerout', () => {
            cell.setTexture('cell')
          })
          this.input.setDraggable(item)
          this.input.on('drag', (pointer: Phaser.Input.Pointer, gameObject: any, dragX: number, dragY: number) => {
            this.counterInventory = 0
            gameObject.x = dragX
            gameObject.y = dragY
          })

          this.input.on('dragend', (pointer: Phaser.Input.Pointer, gameObject: any) => {
            this.counterInventory += 1
            if (this.counterInventory <= 1 && gameObject.texture.key.indexOf('inventory') >= 0) {
              gameObject.x = -100
              gameObject.y = -100

              console.log(gameObject.texture.key)
              const itemName = gameObject.texture.key.replace('inventory', '')
              CONST.SHOP.push(itemName)
              CONST.INVENTORY = CONST.INVENTORY.filter((e) => e !== itemName)
              this.scene.restart()

              mintAlphas()
            }
          })
        }
      }
    }

    // Shop
    for (let j = 0; j < 4; j++) {
      for (let i = 0; i < 4; i++) {
        const cell = this.add.image(900 + i * 130, 250 + j * 130, 'cell')
        const itemName = CONST.SHOP[i + 4 * j]

        if (itemName) {
          const item = this.add.image(900 + i * 130, 250 + j * 130, 'shop' + itemName)
          item.setInteractive({ cursor: 'move' })
          item.setDepth(2)

          item.on('pointerover', () => {
            cell.setTexture('cellHover')
          })
          item.on('pointerout', () => {
            cell.setTexture('cell')
          })
          this.input.setDraggable(item)
          this.input.on('drag', (pointer: Phaser.Input.Pointer, gameObject: any, dragX: number, dragY: number) => {
            this.counterShop = 0
            gameObject.x = dragX
            gameObject.y = dragY
          })

          this.input.on('dragend', (pointer: Phaser.Input.Pointer, gameObject: any) => {
            this.counterShop += 1
            if (this.counterShop <= 1 && gameObject.texture.key.indexOf('shop') >= 0) {
              gameObject.x = -100
              gameObject.y = -100

              console.log(gameObject.texture.key)
              const itemName = gameObject.texture.key.replace('shop', '')
              CONST.INVENTORY.push(itemName)
              CONST.SHOP = CONST.SHOP.filter((e) => e !== itemName)
              this.scene.restart()

              burnAlphas()
            }
          })
        }
      }
    }

    let buttonBack = this.add.image(100, this.sys.canvas.height - 100, 'buttonBack')
    buttonBack.setInteractive({ cursor: 'pointer' })
    buttonBack.on('pointerover', () => buttonBack.setTexture('buttonBackHover'))
    buttonBack.on('pointerout', () => buttonBack.setTexture('buttonBack'))
    buttonBack.on('pointerdown', () => {
      this.sound.add('clickSound').play()
      this.scene.start('Game')
    })
  }

  update(time: number, delta: number): void {
    this.textBalance.setText(`MY BALANCE: ${CONST.ALPHA_BALANCE} ALPHAS`)
  }
}
