import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class SharedService {

  constructor() { }

  setActiveTab(idTab) {
    sessionStorage.setItem("activeTab", JSON.stringify(idTab));
  }

  getActiveTab() {
    return JSON.parse(sessionStorage.getItem("activeTab"));
  }

  destroyActiveTab() {
    sessionStorage.removeItem("activeTab");
  }

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
