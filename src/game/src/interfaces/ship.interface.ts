export interface IShipConstructor {
  scene: Phaser.Scene;
  x: number;
  y: number;
  shipCode: string;
  frame?: string | number;
}
