import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class SharedService {

  constructor(private http: HttpClient) { }

  data;
  setData(data) {
    this.data = data;
   }

  getData() {
    return this.data
  }
}
