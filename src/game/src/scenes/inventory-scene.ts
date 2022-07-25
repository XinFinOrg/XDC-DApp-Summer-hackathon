import { upgradeShip } from '../blockchain/lib'
import { CONST } from '../const/const'

export class Inventory extends Phaser.Scene {
  private counter = 0

  constructor() {
    super({
      key: 'Inventory',
    })
  }

  replaceAt = function (original: string, index: number, replacement: string) {
    return original.substring(0, index) + replacement + original.substring(index + replacement.length)
  }

  init(): void {}

  preload(): void {
  }

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

    this.add.image(this.sys.canvas.width / 2, 80, 'titleInventory')

    const bigCell = this.add.image(0, 0, 'bigCell')
    const partCabin = this.add.image(0, 0, `partCabin${CONST.CURRENT_SHIP.shipCode[0]}`)
    const partEngine = this.add.image(0, 0, `partEngine${CONST.CURRENT_SHIP.shipCode[1]}`)
    const partWing = this.add.image(0, 0, `partWing${CONST.CURRENT_SHIP.shipCode[2]}`)
    const partWeapon = this.add.image(0, 0, `partWeapon${CONST.CURRENT_SHIP.shipCode[3]}`)
    let container = this.add.container(320, 435, [bigCell, partWeapon, partWing, partEngine, partCabin])
    container.setSize(bigCell.width, bigCell.height)

    const cellCabin = this.add.image(350, 220, 'cell')
    const cellEngine = this.add.image(350, 650, 'cell')
    const cellWing = this.add.image(560, 540, 'cell')
    const cellWeapon = this.add.image(560, 330, 'cell')

    const itemCabin = this.add.image(350, 220, `itemCabin${CONST.CURRENT_SHIP.shipCode[0]}`)
    const itemEngine = this.add.image(350, 650, `itemEngine${CONST.CURRENT_SHIP.shipCode[1]}`)
    const itemWing = this.add.image(560, 540, `itemWing${CONST.CURRENT_SHIP.shipCode[2]}`)
    const itemWeapon = this.add.image(560, 330, `itemWeapon${CONST.CURRENT_SHIP.shipCode[3]}`)

    for (let j = 0; j < 4; j++) {
      for (let i = 0; i < 6; i++) {
        const cell = this.add.image(800 + i * 130, 250 + j * 130, 'cell')
        const itemName = CONST.INVENTORY[i + 6 * j]

        if (itemName) {
          const item = this.add.image(800 + i * 130, 250 + j * 130, itemName)
          item.setInteractive({ cursor: 'move' })
          item.setDepth(2)
          let targetCell: Phaser.GameObjects.Image

          if (itemName.indexOf('Cabin') >= 0) targetCell = cellCabin
          if (itemName.indexOf('Weapon') >= 0) targetCell = cellWeapon
          if (itemName.indexOf('Wing') >= 0) targetCell = cellWing
          if (itemName.indexOf('Engine') >= 0) targetCell = cellEngine

          item.on('pointerover', () => {
            cell.setTexture('cellHover')
            targetCell.setTexture('cellHover')
          })
          item.on('pointerout', () => {
            cell.setTexture('cell')
            targetCell.setTexture('cell')
          })
          this.input.setDraggable(item)
          this.input.on('drag', (pointer: Phaser.Input.Pointer, gameObject: any, dragX: number, dragY: number) => {
            gameObject.x = dragX
            gameObject.y = dragY
            this.counter = 0
          })
          this.input.on('dragend', (pointer: Phaser.Input.Pointer, gameObject: any) => {
            this.counter += 1
            if (gameObject.texture.key.indexOf('Cabin') >= 0 && this.counter <= 1) {
              CONST.CURRENT_SHIP.shipCode = this.replaceAt(
                CONST.CURRENT_SHIP.shipCode,
                0,
                gameObject.texture.key.replace('itemCabin', ''),
              )
              itemCabin.setTexture(`itemCabin${CONST.CURRENT_SHIP.shipCode[0]}`)
              partCabin.setTexture(`partCabin${CONST.CURRENT_SHIP.shipCode[0]}`)
              upgradeShip(CONST.CURRENT_SHIP)
            }
            if (gameObject.texture.key.indexOf('Engine') >= 0 && this.counter <= 1) {
              CONST.CURRENT_SHIP.shipCode = this.replaceAt(
                CONST.CURRENT_SHIP.shipCode,
                1,
                gameObject.texture.key.replace('itemEngine', ''),
              )
              itemEngine.setTexture(`itemEngine${CONST.CURRENT_SHIP.shipCode[1]}`)
              partEngine.setTexture(`partEngine${CONST.CURRENT_SHIP.shipCode[1]}`)
              upgradeShip(CONST.CURRENT_SHIP)
            }
            if (gameObject.texture.key.indexOf('Wing') >= 0 && this.counter <= 1) {
              CONST.CURRENT_SHIP.shipCode = this.replaceAt(
                CONST.CURRENT_SHIP.shipCode,
                2,
                gameObject.texture.key.replace('itemWing', ''),
              )
              itemWing.setTexture(`itemWing${CONST.CURRENT_SHIP.shipCode[2]}`)
              partWing.setTexture(`partWing${CONST.CURRENT_SHIP.shipCode[2]}`)
              upgradeShip(CONST.CURRENT_SHIP)
            }
            if (gameObject.texture.key.indexOf('Weapon') >= 0 && this.counter <= 1) {
              CONST.CURRENT_SHIP.shipCode = this.replaceAt(
                CONST.CURRENT_SHIP.shipCode,
                3,
                gameObject.texture.key.replace('itemWeapon', ''),
              )
              itemWeapon.setTexture(`itemWeapon${CONST.CURRENT_SHIP.shipCode[3]}`)
              partWeapon.setTexture(`partWeapon${CONST.CURRENT_SHIP.shipCode[3]}`)
              upgradeShip(CONST.CURRENT_SHIP)
            }

            gameObject.x = -100
            gameObject.y = -100
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
      this.scene.start('Game')})
  }

  update(): void {}
}
