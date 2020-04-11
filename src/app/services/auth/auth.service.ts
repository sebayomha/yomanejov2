import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  constructor(private http: HttpClient) { }

  login(user) {
    return this.http.post('/api/auth/login', user);
  }

  logout(user) {
    return this.http.post<any>('/api/auth/logout', user).pipe(
      tap((data:any) => {
        localStorage.removeItem('uniqueid');
        localStorage.removeItem('uniquert');
      })
    )
  }

  refresh() {
    const refreshToken = localStorage.getItem('uniquert');
    const idUsuario = this.decodePayload().idUsuario;
    const params = {
      idUsuario: idUsuario,
      refreshToken: refreshToken
    }

    return this.http.post('/api/auth/refresh', params).pipe(
      tap((data:any) => {
        this.refreshJWT(data);
      })
    )
  }

  refreshJWT(data) {
    console.log("refresh", data);
    localStorage.setItem('uniqueid', data.jwt);
  }

  isLoggedIn() {
    var uniqueid = localStorage.getItem('uniqueid');
    if(uniqueid != null && uniqueid.valueOf() != '') {
      return true;
    } else {
      localStorage.removeItem('uniqueid');
      localStorage.removeItem('uniquert');
      return false;
    }
  }

  getToken() {
    return localStorage.getItem('uniqueid');
  }

  decodePayload(){
    if( localStorage.getItem('uniqueid') != null && localStorage.getItem('uniqueid').valueOf() != ''){
      try{
        let jwtData = localStorage.getItem('uniqueid').split('.')[1]
        let decodedJwtJsonData = window.atob(jwtData)
        let decodedJwtData = JSON.parse(decodedJwtJsonData)
        return decodedJwtData
      }catch(err){
        return false;
      }
    }
  }
}
