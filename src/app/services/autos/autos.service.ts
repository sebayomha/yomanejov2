import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { BASE_URL } from '../../configVariables';

@Injectable({
  providedIn: 'root',
})
export class AutosService {

  constructor(private http: HttpClient) { }

  obtenerAutos() {
    return this.http.get(`${BASE_URL}/api/autos`);
  }

  obtenerZonas() {
    return this.http.get(`${BASE_URL}/api/autos/zonas`);
  }

  crearAuto(data){
    return this.http.post(`${BASE_URL}/api/autos/crear`, data); 
  }

  modificarAuto(data){
    return this.http.post(`${BASE_URL}/api/autos/modificar`, data); 
  }

  bajarAuto(data) {
    return this.http.post(`${BASE_URL}/api/autos/bajar`, data); 
  }

}
