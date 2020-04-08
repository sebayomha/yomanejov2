export interface LoginUserInterface {
    email: string,
    password: string
}

export class LoginUser implements LoginUserInterface {

    email: string;
    password: string;

    constructor(email: string, password?: string) {
        this.email = email;
        this.password = password;
    }
}