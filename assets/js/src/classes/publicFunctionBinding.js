global.PublicBinding = {};

export function bind(actionName, action){
    PublicBinding[actionName] = action;
}