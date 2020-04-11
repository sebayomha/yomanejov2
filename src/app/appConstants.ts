import { AuthService } from './services/auth/auth.service';
import { HttpClient } from '@angular/common/http';

export class AppSettings {
    public static http: HttpClient;
    public static USER_ROLE = new AuthService(AppSettings.http).decodePayload().role;
}