import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs/operators';
import { BASE_URL } from '../../configVariables';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  constructor(private http: HttpClient) { }

  login(user) {
    return this.http.post(`${BASE_URL}/api/auth/login`, user);
  }

  firstPasswordChange(user) {
    return this.http.post(`${BASE_URL}/api/auth/firstPasswordChange`, user);
  }

  changeForgottenPassword(user) {
    return this.http.post(`${BASE_URL}/api/auth/changeForgottenPassword`, user);
  }

  changePassword(user) {
    return this.http.post(`${BASE_URL}/api/auth/changePassword`, user);
  }

  forgotPasswordEmail(email) {
    return this.http.post(`${BASE_URL}/api/auth/forgotPasswordEmail`, email);
  }

  validForgotPasswordToken(token) {
    return this.http.post(`${BASE_URL}/api/auth/validForgotPasswordToken`, token);
  }

  logout(user) {
    return this.http.post<any>(`${BASE_URL}/api/auth/logout`, user).pipe(
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

    return this.http.post(`${BASE_URL}/api/auth/refresh`, params).pipe(
      tap((data:any) => {
        this.refreshJWT(data);
      })
    )
  }

  refreshJWT(data) {
    localStorage.setItem('uniqueid', data.jwt);
  }

  refreshRT(data) {
    localStorage.setItem('uniquert', data.rt);
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
