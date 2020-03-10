import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class SharedService {

  constructor(private http: HttpClient) { }

  setData(data) {
    sessionStorage.setItem("student",JSON.stringify(data));
   }

  getData() {
    return JSON.parse(sessionStorage.getItem("student"));
  }

  destroyData() {
    sessionStorage.removeItem("student");
  }
}
