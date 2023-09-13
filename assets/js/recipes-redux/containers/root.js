import {Route, Routes} from "react-router-dom";
import React from "react";
import Recipes from "../containers/recipes";
import Recipe from "../containers/recipe";

const Root = () => {

    return (
        <Routes>
            <Route path={"/"} exact element={<Recipes/>}/>
            <Route path={"/recipe/:id"} element={<Recipe/>}/>
        </Routes>
    );
};

export default Root;
