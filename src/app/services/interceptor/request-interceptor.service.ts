import { Injectable } from '@angular/core';
import { HttpErrorResponse, HttpEvent, HttpHandler, HttpRequest } from '@angular/common/http';
import { Observable, throwError, BehaviorSubject } from 'rxjs';
import { finalize, catchError, switchMap, take, filter, retry } from "rxjs/operators";
import { LoaderService } from '../loader/loader-service.service';
import { AuthService } from '../auth/auth.service';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class RequestInterceptorService {

  private isRefreshing: boolean = false;
  private refreshTokenSubject: BehaviorSubject<any> = new BehaviorSubject<any>(null);

  constructor(public loaderService: LoaderService, private authService: AuthService, private _router: Router) { }
  
  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
      this.loaderService.show();
      var idUser = this.authService.decodePayload() ? this.authService.decodePayload().idUsuario: null;
      var request;

      if (idUser != null) {
        request = req.clone({
          setHeaders: {
            Authorization: `Bearer ${this.authService.getToken()}`,
            UserId: idUser
          }
        });
      } else {
        request = req.clone({
          setHeaders: {
            Authorization: `Bearer ${this.authService.getToken()}`
          }
        });
      }

      return next.handle(request).pipe(
        catchError( error => {
          if (error instanceof HttpErrorResponse && error.status == 401) {
            console.log("error")
            if (error.statusText == "Token Expired") {
              return this.handle401ExpiredToken(request, next);
            } else {
              console.log("expiro?")
              this.authService.logout(this.authService.decodePayload().idUsuario);
              this._router.navigate(['login']);
            }
          }
          return throwError(error);
        }),
        finalize(() => this.loaderService.hide()),
      );
  }

  handle401ExpiredToken(request: HttpRequest<any>, next: HttpHandler) {
    if (!this.isRefreshing) {
      console.log("ent2r")

      this.isRefreshing = true;
      this.refreshTokenSubject.next(null);
      
      return this.authService.refresh().pipe(
        switchMap((token: any) => {
          this.isRefreshing = false;
          this.refreshTokenSubject.next(token.jwt);
          console.log("tokenAT", token)
          return next.handle(this.addToken(request, token.jwt));
        })
      )
    } else {
      console.log("Sfaf")
      return this.refreshTokenSubject.pipe(
        filter(token => token != null),
        take(1),
        switchMap(jwt =>{
          console.log("jwt", jwt);
          return next.handle(this.addToken(request, jwt));
        })
      )
    }
  }

  private addToken(request: HttpRequest<any>, token: string) {
    return request.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
  }
}