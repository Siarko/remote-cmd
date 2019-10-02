export let StreamData = new function () {

    let width = 100; //frame width
    let height = 100; //frame height
    let compression = 0.2; //frame compression
    let fpc = 20; //frames per clip

    this.setFPC = (v) => fpc = v;

    this.setResolution = (w,h) => {
        width = w;
        height = h;
    };
    this.setFrameCompression = (v) => compression = v;

    this.getFrameCompression = () => compression;
    this.getFrameWidth = () => width;
    this.getFrameHeight = () => height;
    this.getFramesPerClip = () => fpc;

    return this;
};