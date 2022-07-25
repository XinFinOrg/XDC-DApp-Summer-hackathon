export interface IBulletConstructor {
  scene: Phaser.Scene;
  x: number;
  y: number;
  rotation: number;
  texture: string;
  frame?: string | number;
}
