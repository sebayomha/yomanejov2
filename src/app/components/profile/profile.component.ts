import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { SharedService } from 'src/app/services/sharedService/shared-service';
import { AuthService } from 'src/app/services/auth/auth.service';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.css']
})
export class ProfileComponent implements OnInit {

  constructor(private router: Router, private sharedService: SharedService, private authService: AuthService) { }

  idUsuario: number = null;
  ngOnInit() {
    this.idUsuario = this.authService.decodePayload().idUsuario;
  }

  goToChangePassword() {
    this.sharedService.setData(this.idUsuario);
    this.router.navigate(['perfil/cambiarContraseña']);
  }
}
