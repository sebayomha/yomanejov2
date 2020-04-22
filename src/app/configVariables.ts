import { environment } from '../environments/environment';
export const BASE_URL = (environment.production) ? 'http://192.168.0.99:80' : '';