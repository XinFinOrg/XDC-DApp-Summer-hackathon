import { connectWallet, getShips } from '../blockchain/lib'
import { CONST } from '../const/const'

export class ConnectWallet extends Phaser.Scene {
  private startKey: Phaser.Input.Keyboard.Key
  private bitmapTexts: Phaser.GameObjects.BitmapText[] = []
  private buttonConnectWallet: Phaser.GameObjects.Image
  private showLoading: boolean
  private showingLoading: boolean

  constructor() {
    super({
      key: 'ConnectWallet',
    })
  }

  init(): void {}

  preload(): void {}

  create(): void {
    this.showLoading = false
    this.showingLoading = false

    this.add.tileSprite(
      this.sys.canvas.width / 2,
      this.sys.canvas.height / 2,
      this.sys.canvas.width,
      this.sys.canvas.height,
      'bgHome',
    )

    this.buttonConnectWallet = this.add.image(
      this.sys.canvas.width / 2,
      this.sys.canvas.height / 2 + 240,
      'buttonConnectWallet',
    )
    this.buttonConnectWallet.setSize(this.buttonConnectWallet.width, this.buttonConnectWallet.height)
    this.buttonConnectWallet.setInteractive({ cursor: 'pointer' })
    this.buttonConnectWallet.on(
      'pointerover',
      () => !this.showingLoading && this.buttonConnectWallet.setTexture('buttonConnectWalletHover'),
    )
    this.buttonConnectWallet.on(
      'pointerout',
      () => !this.showingLoading && this.buttonConnectWallet.setTexture('buttonConnectWallet'),
    )
    this.buttonConnectWallet.on('pointerdown', async () => {
      this.sound.add('clickSound').play()
      this.showLoading = true
      await connectWallet()
      await getShips()
      this.scene.start('SelectShip')
    })

    this.sound.add('backgroundMusic').play({ loop: true })
  }

  update(): void {
    if (this.showLoading && !this.showingLoading) {
      this.showingLoading = true
      this.buttonConnectWallet.setTexture('buttonLoading')
    }
  }
}
