import { Component } from '@angular/core';
import { AuthService } from 'src/app/services/auth/auth.service';
import { Router } from '@angular/router';
import { AppSettings } from '../../appConstants';

@Component({
  selector: 'navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})

export class NavbarComponent {

  USER_ROLE;
  constructor(private authService: AuthService, private router: Router) { }

  ngOnInit() { 
    this.USER_ROLE = AppSettings.USER_ROLE;
    console.log("asf", this.USER_ROLE)
  }

  logout() {
    this.authService.logout(this.authService.decodePayload().idUsuario).subscribe( res => {
      if (res.code == 0) {
        this.router.navigate(['login']);
      }
    });
  }

  getUserProfileName() {
    let nombre = this.authService.decodePayload().nombre || null;
    if (nombre) {
      const nombreArray = nombre.split(' ');
      if (nombreArray.length == 2) {
        var inicialApellido = nombreArray[1];
        var nombrePerfil = nombreArray[0].substring(0,1) + '.';
        return inicialApellido + ' ' + nombrePerfil;
      } else { //tiene 3 nombres
        var inicialApellido = nombreArray[2];
        var nombrePerfil1 = nombreArray[0];
        var nombrePerfil2 = nombreArray[0].substring(0,1) + '.';
        return inicialApellido + ' ' + nombrePerfil1 + ' ' + nombrePerfil2;
      }
    } else {
      return '';
    }
  }

  getProfileImagesrc() {
    let id = this.authService.decodePayload().idUsuario || null;
    switch(id) {
      case 1: 
        return 'assets/img/profile/YomhaS.png';
      case 2: 
        return 'assets/img/profile/user_1.png';
      default:
        return 'assets/img/logo.png';
    }
   
  }

}