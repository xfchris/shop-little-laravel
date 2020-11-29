import React, {useState} from 'react';
import ReactDOM from 'react-dom';
import Subcomp from "./Subcomp";
import Subcomp2 from "./Subcomp2";

function Example() {
    const [formData, setFormData] = useState(initialFDState)
    const [vistaForm, setVistaForm] = useState(true)

    let holachris = "chris"
    return (
        <div className="container">
            <div className="row justify-content-center">
                <div className="col-md-8">
                    <div className="card">
                        <div className="card-header">Example Component</div>

                        <div className="card-body">I'm an example component!

                            {vistaForm ?
                                <Subcomp variable={holachris}
                                         formData={formData}
                                         setFormData={setFormData}
                                         setVistaForm={setVistaForm} />:
                                <Subcomp2 formData={formData} setVistaForm={setVistaForm}/>
                                }
                        </div>

                    </div>
                </div>
            </div>
        </div>
    );
}

export default Example;

if (document.getElementById('example')) {
    ReactDOM.render(<Example />, document.getElementById('example'));
}

function initialFDState() {
    return {
        nombre: "Un dato predeterminado",
    }
}
