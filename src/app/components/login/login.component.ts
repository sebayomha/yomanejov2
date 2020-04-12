import { Component, OnInit} from '@angular/core';
import { LoginUser } from '../../models/LoginUser';
import { AuthService } from '../../services/auth/auth.service';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material';
import { Response } from 'src/app/models/response';
import { SnackbarComponent } from '../snackbar/snackbar/snackbar.component';
import { AppSettings } from '../../appConstants';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {

  constructor(private auth: AuthService, private router: Router, private snackbar: MatSnackBar) {  }

  user = {
    password: '',
    email: ''
  };
  errorLogin: boolean;

  message: string;
  errorMsg:string;
  strongMessage: string;
  bannerClass: string;
  bannerDismiss: boolean;

  showPass:boolean;
  dontRememberPass:boolean;
  submitted;

  showNewPasswordBox: boolean;

  userData;
  durationInSeconds: number = 3;

  ngOnInit() { 
    this.showPass = false;
    this.dontRememberPass = false;
    this.showNewPasswordBox = false;
  }

  onSubmit(){
    let usuario = new LoginUser(this.user.email, this.user.password);
    this.auth.login(usuario).subscribe( (data: Response) => {
      if (data.code == 0) { //login exitoso
        localStorage.setItem('uniqueid', data.data.jwt);
        localStorage.setItem('uniquert', data.data.rt);
        AppSettings.refreshRole();
        this.router.navigate(['busqueda'])
      } else{
        switch(data.code) {
          case 1:
          case 3: {
            this.snackbar.openFromComponent(SnackbarComponent, {
              duration: this.durationInSeconds * 1100,
              data: data
            });
          }
          break;
          case 2: { //la contraseña del usuario es la default
            this.errorMsg = "";
            this.userData = {
              'email': this.user.email,
              'name': data.data.name,
              'surname':data.data.surname,
              'iduser': data.data.iduser
            }
            this.showNewPasswordBox = true;
          }
          break;
        }
        this.errorLogin = true; //login fallido
      }   
    });
  }

  showPassword(){
    this.showPass = !this.showPass;
  }

  dontRememberPassword(){
    this.dontRememberPass = !this.dontRememberPass;
  }
  
  showNewPassBox(){
    this.showNewPasswordBox = !this.showNewPasswordBox;
  }
}
