import { AuthService } from './services/auth/auth.service';
import { HttpClient } from '@angular/common/http';

export class AppSettings {
    public static http: HttpClient;
    public static isLoggedIn = new AuthService(AppSettings.http).isLoggedIn();
    public static USER_ROLE = AppSettings.isLoggedIn ? new AuthService(AppSettings.http).decodePayload().role: null;

    public static refreshRole() {
        AppSettings.USER_ROLE = new AuthService(AppSettings.http).decodePayload().role;
    }
}
