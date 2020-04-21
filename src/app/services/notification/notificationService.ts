import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BASE_URL } from '../../configVariables';

@Injectable({
  providedIn: 'root',
})
export class NotificationsService {

  constructor(private http: HttpClient) { }

  addPushSubscriber(sub) {
    return this.http.post(`${BASE_URL}/api/notifications/pushSubscriber`, sub);
  }
}