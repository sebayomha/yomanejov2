import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { Observable } from 'rxjs';
import { AuthService } from '../auth/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuardService implements CanActivate {

  constructor(private authService: AuthService, private _router: Router) { }

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    //Si esta logueado, entonces lo dejo ingresar al sitio, sino, lo devuelvo al login.
    const isLoginPage = route.data.isLoginPage;

    if (isLoginPage) {
        if(this.authService.isLoggedIn()){
            this._router.navigate(['busqueda']);
            return false;
        } else {
            return true;
        }
    } else {
        if(!this.authService.isLoggedIn()){
            this._router.navigate(['login']);
            return false;
        } else {
            if(!route.data.authRole || (this.authService.decodePayload().role == route.data.authRole))
                return true;
            this._router.navigate(['busqueda']);
            return false;
        }
    }      
  }
}
