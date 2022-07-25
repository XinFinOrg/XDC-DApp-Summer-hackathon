import { CONST } from '../const/const'
import { IDropConstructor } from '../interfaces/drop.interface'

export class Drop extends Phaser.Physics.Arcade.Sprite {
  body: Phaser.Physics.Arcade.Body

  public getBody(): any {
    return this.body
  }

  constructor(aParams: IDropConstructor) {
    super(aParams.scene, aParams.x, aParams.y, aParams.texture)

    // init drop
    this.x = aParams.x
    this.y = aParams.y

    this.scene.physics.world.enable(this)
    this.body.allowGravity = false
    this.body.setSize(CONST.ITEM_SIZE, CONST.ITEM_SIZE)
    this.body.setOffset(CONST.ITEM_SIZE / 2, CONST.ITEM_SIZE / 2)

    this.scene.add.existing(this)
  }

  update(): void {}
}
