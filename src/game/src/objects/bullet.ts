import { IBulletConstructor } from '../interfaces/bullet.interface'

export class Bullet extends Phaser.Physics.Arcade.Sprite {
  body: Phaser.Physics.Arcade.Body

  private velocity: Phaser.Math.Vector2
  private lifeSpan: number
  private isOffScreen: boolean

  public getBody(): any {
    return this.body
  }

  constructor(aParams: IBulletConstructor) {
    super(aParams.scene, aParams.x, aParams.y, aParams.texture)

    // variables
    this.lifeSpan = 100
    this.isOffScreen = false

    this.setCrop(90, 0, 90, 70)

    // init bullet
    this.x = aParams.x
    this.y = aParams.y
    this.velocity = new Phaser.Math.Vector2(
      15 * Math.cos(aParams.rotation - Math.PI / 2),
      15 * Math.sin(aParams.rotation - Math.PI / 2),
    )
    this.rotation = aParams.rotation + Math.PI / 2

    // physics
    this.scene.physics.world.enable(this)
    this.body.allowGravity = false
    this.body.setCircle(3)
    this.body.setOffset(140, 35)
    this.scene.add.existing(this)
  }

  update(): void {
    // apple velocity to position
    this.x += this.velocity.x
    this.y += this.velocity.y

    if (this.lifeSpan < 0 || this.isOffScreen) {
      this.setActive(false)
    } else {
      this.lifeSpan--
    }

    this.checkIfOffScreen()
  }

  private checkIfOffScreen(): void {
    if (this.x > this.scene.sys.canvas.width + 1 || this.y > this.scene.sys.canvas.height + 1) {
      this.isOffScreen = true
    }
  }
}
