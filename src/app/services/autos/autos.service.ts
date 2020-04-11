import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class AutosService {

  constructor(private http: HttpClient) { }

  obtenerAutos() {
    return this.http.get('api/autos');
  }

}