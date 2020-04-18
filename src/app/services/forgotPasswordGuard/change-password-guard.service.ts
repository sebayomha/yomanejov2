import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { AuthService } from '../auth/auth.service';

@Injectable({
  providedIn: 'root'
})
export class ChangePasswordGuardService implements CanActivate {

  constructor(private authService: AuthService, private _router: Router) { }

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
  
    var token = state.url.split("/").pop();
    return this.authService.validForgotPasswordToken(token).toPromise().then( (res:any) => {
      if (res.code == 0) {
        return true;
      } else {
        return false;
      }
    }).catch(err => {
      return false;
    });
  }
}
