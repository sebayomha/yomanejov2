import { Component, OnInit, ViewChild, Output, ElementRef, EventEmitter} from '@angular/core';
import { LoginUser } from '../../models/LoginUser';
import { AuthService } from '../../services/auth/auth.service';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material';
import { Response } from 'src/app/models/response';

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

  @ViewChild('password') password: ElementRef<HTMLElement>;

  ngOnInit() { 
    this.showPass = false;
    this.dontRememberPass = false;
    this.showNewPasswordBox = false;
  }

  onSubmit(){
    let usuario = new LoginUser(this.user.email, this.user.password);
    this.auth.login(usuario).subscribe( (data: Response) => {
      if(data.code == 0){ //login exitoso
        //Guardo la informacion del usuario en localstorage para manipular la info durante toda la aplicacion.
        localStorage.setItem('uniqueid', data.data.jwt);
        //Redirecciono al Home.
        this.router.navigate(['clases'])
      }else{
        switch(data.code){
          case 6500: 
          this.errorMsg = "Usuario o contraseña incorrecta";
          break;
          case 7000: 
          this.errorMsg = "Usuario o contraseña incorrecta";
          break;
          case 4000:
          this.errorMsg = "El correo y la contraseña son mandatorios";
          break;
          case 8000: { //la contraseña del usuario es la default
            this.errorMsg = "";
            this.userData = {
              'email': this.user.email,
              'name': data.data.name,
              'surname':data.data.surname,
              'iduser': data.data.iduser
            }
            localStorage.setItem('userData', JSON.stringify(this.userData));
            this.showNewPasswordBox = true;
          }
          break;
        }
        //login fallido
        this.errorLogin = true;
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

  changePassword($event){
    let user = new LoginUser(this.userData.email);
    user.setUser(this.userData.email, this.userData.name, this.userData.surname, this.userData.iduser);
    this.userService.changeFirstPassword($event.password, user).subscribe( (data:any) =>{
      if(data.code == 0){ //cambio de contraseña exitoso
        this.openSnackBar("Contraseña actualizada correctamente", "success", false, true, $event.password);
      }else{ //Ocurrio un error
        this.openSnackBar(data.message, "error", false, false);
      }
    });
  }

  openSnackBar(message, bannerClass, action, generateActionAfterDismiss, newPassword?){
    let usuario = new LoginUser(this.user.email, newPassword);
    this.snackbar.openFromComponent(BannerMessageComponent, {
      duration: 3000,
      panelClass: 'successMessage',
      data: {message: message, bannerClass: bannerClass, action: action, snackbar: this.snackbar}
    }).afterDismissed().subscribe( () => { //Hacer login una vez que desaparezca el mensaje
        if(generateActionAfterDismiss) //si lo que ocurrio fue un cambio de contraseña exitoso intento el login
          this.auth.login(usuario).subscribe( (data: Response) => {
            if(data.code == 0){
              localStorage.setItem('uniqueid', data.data.jwt);
              this.router.navigate(['clases'])
            }            
        })       
  })
  }

}
