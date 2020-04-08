import { Injectable } from '@angular/core';
import { HttpEvent, HttpHandler, HttpInterceptor, HttpRequest, HttpErrorResponse } from "@angular/common/http";
import { Observable, throwError } from "rxjs";
import { finalize, catchError } from "rxjs/operators";
import { LoaderService } from '../loader/loader-service.service';

@Injectable({
  providedIn: 'root'
})
export class RequestInterceptorService {

  constructor(public loaderService: LoaderService) { }
    intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        this.loaderService.show();
        return next.handle(req).pipe(
            catchError((error: HttpErrorResponse) => {
              if (error.status == 401)
               return throwError(error);
            }),
            finalize(() => this.loaderService.hide())
        );
    }
}